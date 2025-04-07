<?php

namespace App\Models;

use App\Models\BaseModel;

class CurrencyModel extends BaseModel
{
    protected $table = 'currencies';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',           // Ví dụ: "Việt Nam Đồng"
        'abbreviation',   // Ví dụ: "VND"
        'symbol',         // Ví dụ: "₫"
        'created_at',
        'updated_at'
    ];
}
