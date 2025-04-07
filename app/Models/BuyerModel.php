<?php namespace App\Models;

use App\Models\BaseModel;

class BuyerModel extends BaseModel
{
    protected $table      = 'buyers';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'name',
        'address',
        'created_at',
        'updated_at'
    ];
    
}
