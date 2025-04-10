<?php

namespace App\Models;

use App\Models\BaseModel;

class PurposeModel extends BaseModel
{
    protected $table = 'custom_purposes';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'voucher_date',    // Ngày lập phiếu (DATE)
        'purpose_name',    // Tên mục đích (VARCHAR)
        'amount',          // Số tiền liên quan (DECIMAL)
        'remaining_amount',// Số tiền chưa thanh toán (DECIMAL)
        'description',     // Mô tả phiếu theo dõi (TEXT)
        'created_by',      // Người lập (INT, FK users.id)
        'voucher_number'   // Số phiếu (VARCHAR)
    ];

    /**
     * Lấy số phiếu tiếp theo theo định dạng <năm lập><tháng lập>-<số tăng dần>
     * Số tăng dần được tính theo tháng: luôn lấy giá trị số cao nhất hiện có + 1.
     *
     * @param string $voucher_date Ngày lập phiếu (YYYY-MM-DD)
     * @return string Giá trị số phiếu theo định dạng, ví dụ: 202504-15
     */
    public function getNextVoucherNumber($voucher_date)
    {
        // Lấy năm và tháng từ ngày lập phiếu
        $year  = date('Y', strtotime($voucher_date));
        $month = date('m', strtotime($voucher_date));
        $prefix = $year . $month; // Ví dụ: "202504"
    
        $builder = $this->builder();
        // Truy vấn số tăng dần cao nhất theo điều kiện voucher_number LIKE '<prefix>-%'
        // Lấy số sau dấu '-' bằng cách dùng SUBSTRING, và ép sang kiểu số (CAST ... AS UNSIGNED)
        $builder->select("MAX(CAST(SUBSTRING(voucher_number, " . (strlen($prefix) + 2) . ") AS UNSIGNED)) as max_number", false);
        $builder->like('voucher_number', $prefix . '-', 'after');
        $result = $builder->get()->getRowArray();
    
        // Nếu không có phiếu nào, số tăng dần là 1, ngược lại là số cao nhất + 1
        $next = empty($result['max_number']) ? 1 : $result['max_number'] + 1;
        
        // Định dạng số tăng dần thành chuỗi 4 ký tự, thêm số 0 ở đầu nếu cần
        $nextFormatted = sprintf('%04d', $next);
        return $prefix . '-' . $nextFormatted;
    }
    
}
