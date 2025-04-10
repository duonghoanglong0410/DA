<?php

namespace App\Models;

use App\Models\BaseModel;

class CashJournalModel extends BaseModel
{
    protected $table = 'custom_cash_journals';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'created_date',
        'transaction_type',
        'amount',
        'description',
        'created_by'
    ];


    /**
     * Lấy danh sách công nợ đã trả liên quan đến phiếu theo dõi (purpose_id)
     * Đồng thời join với bảng users để lấy thông tin người tạo phiếu thanh toán.
     *
     * @param int $purposeId
     * @return array
     */
    public function getSettlementsByPurpose($purposeId)
    {
        $builder = $this->db->table($this->table . ' AS ccj');
        // Sử dụng DATE_FORMAT để định dạng ngày theo dd-mm-yyyy
        $builder->select('ccj.*, u.fullname as creator_fullname, u.username as creator_username, DATE_FORMAT(ccj.created_date, "%d-%m-%Y") as created_date', false);
        $builder->join('users AS u', 'ccj.created_by = u.id', 'left');
        $builder->where('ccj.purpose_id', $purposeId);
        return $builder->get()->getResultArray();
    }    

    // Lấy danh sách phiếu theo dõi chưa được thanh toán đầy đủ
    public function getUnsettledFollowups()
    {
        $builder = $this->builder();
        $builder->where('transaction_type', 'chi');
        $results = $builder->get()->getResultArray();
        foreach ($results as &$voucher) {
            $settled = $this->getSettledAmount($voucher['id']);
            $voucher['unsettled_amount'] = $voucher['amount'] - $settled;
        }
        return $results;
    }

    // Tính tổng số tiền đã thanh toán cho một phiếu theo dõi
    public function getSettledAmount($voucherId)
    {
        $db = \Config\Database::connect();
        $query = $db->query("SELECT SUM(settlement_amount) as total FROM custom_debt_settlements WHERE followup_voucher_id = ?", [$voucherId]);
        $row = $query->getRowArray();
        return $row['total'] ? $row['total'] : 0;
    }


    // Lấy danh sách phiếu theo dõi theo tiêu chuẩn FIFO (sắp xếp theo ngày tạo)
    public function getUnsettledFollowupsFIFO()
    {
        $builder = $this->builder();
        $builder->where('transaction_type', 'chi')
                ->orderBy('created_date', 'ASC');
        $results = $builder->get()->getResultArray();
        foreach ($results as &$voucher) {
            $settled = $this->getSettledAmount($voucher['id']);
            $voucher['unsettled_amount'] = $voucher['amount'] - $settled;
        }
        return $results;
    }

    // Lấy tổng số tiền theo loại giao dịch (thu hoặc chi)
    public function getTotalByType($type)
    {
        $builder = $this->builder();
        $builder->select('SUM(amount) as total');
        $builder->where('transaction_type', $type);
        $result = $builder->get()->getRowArray();
        return $result['total'] ? $result['total'] : 0;
    }

    // Tổng công nợ chưa trả từ các phiếu theo dõi
    public function getTotalUnsettledFollowup()
    {
        $followups = $this->getFollowupList();
        $total = 0;
        foreach ($followups as $voucher) {
            $total += $voucher['unsettled_amount'];
        }
        return $total;
    }
}
