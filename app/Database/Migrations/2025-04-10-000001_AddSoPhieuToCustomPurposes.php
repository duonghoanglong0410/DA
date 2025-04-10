<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSoPhieuToCustomPurposes extends Migration
{
    public function up()
    {
        // Thêm cột "so_phieu" chứa số phiếu tự động tạo theo định dạng <năm lập><tháng lập>-<số tăng dần>
        // Ví dụ: 202504-15
        $fields = [
            'voucher_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => 'Số phiếu tự động tạo theo định dạng <năm lập><tháng lập>-<số tăng dần>; số tăng dần được tính theo tháng, luôn lấy số cao nhất + 1 để tránh dùng lại số trống do phiếu bị xoá'
            ],
        ];
        $this->forge->addColumn('custom_purposes', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('custom_purposes', 'voucher_number');
    }
}
