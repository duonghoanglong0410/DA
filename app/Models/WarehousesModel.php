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
     * Nếu có nhiều người quản lý, sử dụng GROUP_CONCAT để nối tên lại với nhau.
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
     * Ở bảng warehouse_product_categories, cột product_categories_stock đã được đổi tên thành stock.
     *
     * @param array $data Dữ liệu của kho mới.
     * @return bool Trả về true nếu thành công, ngược lại trả về false.
     */
    public function addWarehouseAndCategories(array $data)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Thêm bản ghi vào bảng warehouses (Ghi chú: tên kho, địa chỉ của kho)
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
                    'warehouse_id'        => $newWarehouseId,    // Kho được thêm vào
                    'product_category_id' => $category['id'],    // ID của danh mục sản phẩm
                    'stock'               => 0,    // Số lượng tồn (mặc định 0)
                    'avg_price'           => 0,    // Đơn giá trung bình (mặc định 0)
                    'created_at'          => $currentDate,       // Thời gian tạo
                    'updated_at'          => $currentDate,       // Thời gian cập nhật
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

    /**
     * Kiểm tra điều kiện có thể xoá kho hay không.
     * Điều kiện:
     * - Tất cả bản ghi trong bảng warehouse_product_categories của kho đó phải có stock = 0.
     * - Kho không xuất hiện trong bảng trip_details qua cột warehouse_id.
     *
     * @param int $warehouseId ID của kho cần kiểm tra.
     * @return bool Trả về true nếu thỏa điều kiện xoá, false nếu không.
     */
    public function canDeleteWarehouse($warehouseId)
    {
        $db = \Config\Database::connect();

        // Kiểm tra điều kiện 1: Tồn tại bản ghi trong warehouse_product_categories mà stock khác 0
        $builder = $db->table('warehouse_product_categories');
        $builder->selectCount('id', 'tong');
        $builder->where('warehouse_id', $warehouseId);
        $builder->where('stock !=', 0); // Ghi chú: stock của danh mục hàng tồn phải bằng 0
        $query = $builder->get();
        $row = $query->getRow();
        if ($row && $row->tong > 0) {
            return false;
        }

        // Kiểm tra điều kiện 2: Kho không được xuất hiện trong bảng trip_details qua cột warehouse_id
        $builder2 = $db->table('trip_details');
        $builder2->selectCount('id', 'tong');
        $builder2->where('warehouse_id', $warehouseId);
        $query2 = $builder2->get();
        $row2 = $query2->getRow();
        if ($row2 && $row2->tong > 0) {
            return false;
        }

        return true;
    }
}
