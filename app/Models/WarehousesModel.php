<?php namespace App\Models;

use App\Models\BaseModel;
use Exception;

class WarehousesModel extends BaseModel
{
    protected $table         = 'warehouses';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'address', 'created_at', 'updated_at'];

    /**
     * Thêm kho mới và tự động tạo các bản ghi trong bảng warehouse_product_categories
     * cho tất cả các product_categories hiện có.
     *
     * @param array $data Dữ liệu của kho mới.
     * @return bool Trả về true nếu thành công, ngược lại trả về false.
     */
    public function addWarehouseAndCategories(array $data)
    {
        // Lấy đối tượng kết nối cơ sở dữ liệu từ Config
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Thêm bản ghi vào bảng warehouses
            $this->insert($data);
            $newWarehouseId = $this->getInsertID();

            // Lấy danh sách tất cả các product_categories thông qua ProductCategoryModel
            $productCategoryModel = new ProductCategoryModel();
            $categories = $productCategoryModel->findAll();

            // Khởi tạo model của warehouse_product_categories để thêm các bản ghi liên quan
            $warehouseProductCategoriesModel = new WarehouseProductCategoriesModel();
            $currentDate = date('Y-m-d H:i:s');
            foreach ($categories as $category) {
                $wpData = [
                    'warehouse_id'             => $newWarehouseId,
                    'product_category_id'      => $category['id'],
                    'stock'                    => 0,   // Mặc định 0
                    'avg_price'                => 0,   // Mặc định 0
                    'created_at'               => $currentDate,
                    'updated_at'               => $currentDate,
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
