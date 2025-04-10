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
        // Lấy năm và tháng từ ngày lập
        $year  = date('Y', strtotime($voucher_date));
        $month = date('m', strtotime($voucher_date));
        $prefix = $year . $month; // Ví dụ: "202504"

        // Sử dụng query để lấy số tăng dần cao nhất với điều kiện voucher_number LIKE '<prefix>-%'
        // Lưu ý: cần loại bỏ phần prefix và dấu '-' để so sánh số
        $builder = $this->builder();
        // Truy vấn MAX bằng cách dùng SUBSTRING: vị trí bắt đầu là LENGTH(prefix) + 2 (vì có dấu '-')
        // Chú ý: phương thức CAST(... AS UNSIGNED) để chuyển chuỗi số về kiểu số (với MySQL)
        $builder->select("MAX(CAST(SUBSTRING(voucher_number, " . (strlen($prefix) + 2) . ") AS UNSIGNED)) as max_number", false);
        $builder->like('voucher_number', $prefix . '-', 'after');
        $result = $builder->get()->getRowArray();

        $next = empty($result['max_number']) ? 1 : $result['max_number'] + 1;
        return $prefix . '-' . $next;
    }
}
