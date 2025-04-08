<?php namespace App\Models;

use App\Models\BaseModel;

class WarehouseProductCategoriesModel extends BaseModel
{
    protected $table      = 'warehouse_product_categories';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'warehouse_id',
        'product_category_id',
        'stock',
        'avg_price',
        'created_at',
        'updated_at'
    ];
}
