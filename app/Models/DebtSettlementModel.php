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

    /**
     * Lấy danh sách settlement liên quan đến phiếu theo dõi (custom_purposes)
     * bằng cách truy vấn bảng custom_debt_settlements, join với custom_cash_journals và users.
     *
     * @param int $purposeId ID của phiếu theo dõi
     * @return array
     */
    public function getSettlementsByPurpose($purposeId)
    {
        $builder = $this->db->table($this->table . ' AS ds');
        // Query truy xuất thông tin settlement:
        // - ds.settlement_amount: số tiền thanh toán
        // - cv.created_date: ngày lập phiếu thanh toán (sẽ được format trong query thành formatted_date)
        // - cv.description: mô tả phiếu thanh toán -> alias payment_description
        // - u.fullname, u.username: thông tin người lập phiếu thanh toán
        $builder->select(
            'ds.settlement_amount, ds.created_at, cv.created_date, cv.description as payment_description, ' .
            'u.fullname as creator_fullname, u.username as creator_username, ' .
            'DATE_FORMAT(cv.created_date, "%d-%m-%Y") as formatted_date',
            false
        );
        $builder->join('custom_cash_journals AS cv', 'ds.payment_voucher_id = cv.id', 'left');
        $builder->join('users AS u', 'cv.created_by = u.id', 'left');
        $builder->where('ds.followup_voucher_id', $purposeId);
        return $builder->get()->getResultArray();
    }

    /**
     * Tính tổng số tiền đã thanh toán cho phiếu theo dõi có ID là $purposeId.
     *
     * @param int $purposeId
     * @return float
     */
    public function getTotalSettledAmount($purposeId)
    {
        $builder = $this->db->table($this->table);
        $builder->select("SUM(settlement_amount) as total", false);
        $builder->where('followup_voucher_id', $purposeId);
        $result = $builder->get()->getRowArray();
        return !empty($result['total']) ? (float)$result['total'] : 0.0;
    }    

    /**
     * Lấy danh sách phiếu theo dõi (followup vouchers) liên kết với phiếu thanh toán (payment voucher)
     * qua khóa ngoại: custom_debt_settlements.followup_voucher_id → custom_purposes.id.
     *
     * @param int $paymentVoucherId
     * @return array Mảng các bản ghi với keys: followup_id, voucher_number
     */
    public function getFollowupVouchersByPayment($paymentVoucherId)
    {
        $builder = $this->db->table($this->table . ' AS ds');
        $builder->select('cp.id as followup_id, cp.voucher_number', false);
        $builder->join('custom_purposes AS cp', 'ds.followup_voucher_id = cp.id', 'left');
        $builder->where('ds.payment_voucher_id', $paymentVoucherId);
        $result = $builder->get()->getResultArray();
        return $result;
    }    
}
