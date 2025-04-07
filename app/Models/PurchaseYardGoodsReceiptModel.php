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
    
}
