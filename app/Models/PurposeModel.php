<?php

namespace App\Models;

use App\Models\BaseModel;

class PurposeModel extends BaseModel
{
    protected $table = 'custom_purposes';
    protected $primaryKey = 'id';
    // Bổ sung thêm các trường cần thiết cho phiếu theo dõi:
    // voucher_date: ngày lập phiếu (kiểu DATE)
    // purpose_name: tên mục đích
    // amount: số tiền
    // description: mô tả
    // created_by: người lập
    protected $allowedFields = [
        'voucher_date',
        'purpose_name',
        'amount',
        'description',
        'created_by'
    ];
}
