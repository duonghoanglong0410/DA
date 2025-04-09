<?php

namespace App\Models;

use App\Models\BaseModel;

class DebtSettlementModel extends BaseModel
{
    protected $table = 'custom_debt_settlements';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'payment_voucher_id',
        'followup_voucher_id',
        'settlement_amount'
    ];
}
