<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRemainingAmountToCustomPurposes extends Migration
{
    public function up()
    {
        // Thêm cột "remaining_amount" lưu số tiền chưa thanh toán
        $fields = [
            'remaining_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
                'null'       => false,
                'comment'    => 'Số tiền còn lại chưa thanh toán của phiếu theo dõi'
            ],
        ];
        $this->forge->addColumn('custom_purposes', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('custom_purposes', 'remaining_amount');
    }
}
