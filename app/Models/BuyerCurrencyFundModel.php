<?php namespace App\Models;

use App\Constants\VoucherTypes;
use App\Models\BaseModel;
use App\Models\TripModel;
use App\Models\CashVoucherModel;

class BuyerCurrencyFundModel extends BaseModel
{
    protected $table      = 'buyer_currency_funds';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'buyer_id',
        'currency_id',
        'remaining_debt',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;

    /**
     * Kiểm tra xem quỹ tiền của nhà máy có được tham chiếu trong bảng chuyến xe (trips) hoặc
     * trong bảng phiếu thu tiền (cash_vouchers với voucher_type = 201) hay không.
     * Hàm sẽ sử dụng các model TripModel và CashVoucherModel để đếm số bản ghi liên quan.
     * Nếu số bản ghi > 0 thì trả về true, ngược lại trả về false.
     *
     * @param int $fundId
     * @return bool
     */
    public function isReferenced($fundId)
    {
        $tripModel = new TripModel();
        $cashVoucherModel = new CashVoucherModel();
        $tripCount = $tripModel->countByCurrencyFundId($fundId);
        $voucherCount = $cashVoucherModel->countBySendingFundIdAndVoucherType($fundId, VoucherTypes::CUSTOMER_COLLECTION);
        return ($tripCount > 0 || $voucherCount > 0);
    }
}
