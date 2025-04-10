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
        'purpose_id',
        'is_followup',
        'created_by'
    ];

    /**
     * Kiểm tra phiếu theo dõi có xuất hiện trong bảng custom_cash_journals hay không
     *
     * @param int $purpose_id ID của phiếu theo dõi trong bảng custom_purposes
     * @return bool true nếu phiếu được sử dụng, false nếu chưa được sử dụng
     */
    public function isPurposeUsed($purpose_id)
    {
        $builder = $this->db->table($this->table);
        $builder->where('purpose_id', $purpose_id);
        $count = $builder->countAllResults();
        return ($count > 0);
    }    

    // Lấy danh sách phiếu theo dõi chưa được thanh toán đầy đủ
    public function getUnsettledFollowups()
    {
        $builder = $this->builder();
        $builder->where('transaction_type', 'chi')
                ->where('is_followup', 1);
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
                ->where('is_followup', 1)
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
