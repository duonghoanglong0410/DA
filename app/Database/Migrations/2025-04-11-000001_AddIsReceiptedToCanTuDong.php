<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsReceiptedToCanTuDong extends Migration
{
    public function up()
    {
        // Thêm cột is_receipted vào bảng CAN_TU_DONG
        $this->forge->addColumn('can_tu_dong', [
            'is_receipted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => 'Đánh dấu phiếu cân đã được lập phiếu nhập hàng hay chưa'
            ]
        ]);
    }

    public function down()
    {
        // Xóa cột is_receipted khi rollback
        $this->forge->dropColumn('can_tu_dong', 'is_receipted');
    }
} 