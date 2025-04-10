<?php

namespace App\Models;

use App\Models\BaseModel;

class CustomsExpenseSummaryModel extends BaseModel
{
    protected $table            = 'customs_expense_summary';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'balance',
    ];
    
    /**
     * Cập nhật hoặc tạo mới bản ghi tổng hợp chi phí hải quan cho một kỳ
     *
     * @param string $period Kỳ báo cáo (YYYY-MM-DD)
     * @param float $totalIncome Tổng thu
     * @param float $totalExpense Tổng chi
     * @param float $totalDebt Tổng công nợ
     * @return bool Kết quả cập nhật/tạo mới
     */
    public function updateOrCreate($balance)
    {
        
        // Kiểm tra xem kỳ báo cáo đã tồn tại chưa
        $existing = $this->first();
        
        $data = [
            'balance'       => $balance,
        ];
        
        if ($existing) {
            // Cập nhật bản ghi hiện có
            return $this->update($existing['id'], $data);
        } else {
            // Tạo bản ghi mới
            return $this->insert($data);
        }
    }
    
    /**
     * Lấy thông tin tổng hợp chi phí hải quan mới nhất
     *
     * @return array Thông tin tổng hợp
     */
    public function getLatestSummary()
    {
        $summary = $this->first();
        
        if (!$summary) {
            // Trả về dữ liệu mặc định nếu không có bản ghi nào
            return [
                'balance'       => 0,
            ];
        }
        
        return $summary;
    }
}
