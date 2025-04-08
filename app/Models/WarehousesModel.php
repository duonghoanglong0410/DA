<?php namespace App\Models;

use App\Models\BaseModel;
use App\Models\WarehouseProductCategoriesModel;
use App\Models\ProductCategoryModel;
use Exception;

class WarehousesModel extends BaseModel
{
    protected $table         = 'warehouses';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'address', 'created_at', 'updated_at'];

    /**
     * Lấy danh sách kho kèm theo tên người quản lý (fullname).
     * Nếu có nhiều người quản lý thì sử dụng GROUP_CONCAT để nối các tên lại với nhau.
     *
     * @return array Danh sách kho với thông tin người quản lý.
     */
    public function getWarehousesWithManagers()
    {
        return $this->select("warehouses.*, GROUP_CONCAT(users.fullname SEPARATOR ', ') as managers", false)
                    ->join('user_role_assignments', 'user_role_assignments.warehouse_id = warehouses.id', 'left')
                    ->join('users', 'user_role_assignments.user_id = users.id', 'left')
                    ->groupBy('warehouses.id')
                    ->findAll();
    }

    /**
     * Thêm kho mới và tự động tạo các bản ghi trong bảng warehouse_product_categories
     * cho tất cả các product_categories hiện có.
     * (Ở bảng warehouse_product_categories, cột product_categories_stock đã được đổi tên thành stock).
     *
     * @param array $data Dữ liệu của kho mới.
     * @return bool Trả về true nếu thành công, ngược lại trả về false.
     */
    public function addWarehouseAndCategories(array $data)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Thêm bản ghi vào bảng warehouses
            $this->insert($data);
            $newWarehouseId = $this->getInsertID();

            // Lấy tất cả các product_categories thông qua ProductCategoryModel
            $productCategoryModel = new ProductCategoryModel();
            $categories = $productCategoryModel->findAll();

            // Khởi tạo model WarehouseProductCategoriesModel (đã được import)
            $warehouseProductCategoriesModel = new WarehouseProductCategoriesModel();
            $currentDate = date('Y-m-d H:i:s');
            foreach ($categories as $category) {
                $wpData = [
                    'warehouse_id'        => $newWarehouseId,
                    'product_category_id' => $category['id'],
                    'stock'               => 0,   // Sửa tên cột từ product_categories_stock thành stock
                    'avg_price'           => 0,
                    'created_at'          => $currentDate,
                    'updated_at'          => $currentDate,
                ];
                $warehouseProductCategoriesModel->insert($wpData);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return false;
            } else {
                $db->transCommit();
                return true;
            }
        } catch (Exception $e) {
            $db->transRollback();
            return false;
        }
    }
}
