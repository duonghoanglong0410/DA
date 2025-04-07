<?php namespace App\Models;

use App\Models\BaseModel;

class ProductCategoryModel extends BaseModel
{
    protected $table      = 'product_categories';
    protected $primaryKey = 'id';
    
    // Các cột được phép thao tác
    protected $allowedFields = [
        'name',
        'abbreviation',
        'created_at',
        'updated_at'
    ];

}
