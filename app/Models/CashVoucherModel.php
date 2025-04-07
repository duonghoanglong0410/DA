<?php namespace App\Models;

use App\Models\BaseModel;

class CashVoucherModel extends BaseModel
{
    protected $table      = 'cash_vouchers';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'sending_currency_fund_id',
        'receiving_currency_fund_id',
        'amount',
        'type',
        'voucher_type',  // Giả sử bảng có cột voucher_type để phân loại
        'created_by',
        'confirmed_by',
        'approved_by',
        'status',
        'created_at',
        'updated_at'
    ];
    
    // Đếm số bản ghi trong cash_vouchers có sending_currency_fund_id bằng $fundId và voucher_type = $voucherType
    public function countBySendingFundIdAndVoucherType($fundId, $voucherType)
    {
        return $this->where('sending_currency_fund_id', $fundId)
                    ->where('voucher_type', $voucherType)
                    ->countAllResults();
    }
}
