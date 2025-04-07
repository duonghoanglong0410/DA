<?php

namespace App\Models;

use App\Models\BaseModel;

class PurchaseYardsModel extends BaseModel
{
    protected $table = 'purchase_yards';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'yard_code',      // Mã bãi thu mua
        'yard_name',      // Tên bãi
        'client_api_key', // Client API Key
        'status',         // Trạng thái
        'created_at',
        'updated_at'
    ];
}
