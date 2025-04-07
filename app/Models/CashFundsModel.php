<?php

namespace App\Models;

use App\Models\BaseModel;

class CashFundsModel extends BaseModel
{
    protected $table = 'cash_funds';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'fund_code',   // Mã quỹ tiền mặt
        'fund_name',   // Tên quỹ tiền mặt
        'created_at',
        'updated_at'
    ];
}
