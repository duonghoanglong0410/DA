<?php namespace App\Models;

use App\Models\BaseModel;

class PurchaseYardGoodsReceiptModel extends BaseModel
{
    protected $table      = 'purchase_yard_goods_receipts';
    protected $primaryKey = 'id';
    
    // Các cột được phép thao tác theo cấu trúc bảng
    protected $allowedFields = [
        'yard_id',
        'input_date',
        'vehicle_number',
        'category_id',
        'quantity',
        'unit_price',
        'created_by',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Thêm mới phiếu nhập hàng
     *
     * @param array $data Dữ liệu phiếu nhập hàng
     * @return int ID của phiếu mới thêm
     */
    public function insert($data = null, bool $returnID = true)
    {
        // Thêm thời gian tạo và cập nhật
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return parent::insert($data, $returnID);
    }
}
