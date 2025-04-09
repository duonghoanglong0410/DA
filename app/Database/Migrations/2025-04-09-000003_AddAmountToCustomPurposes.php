<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAmountToCustomPurposes extends Migration
{
    public function up()
    {
        // Thêm cột "amount" chứa số tiền vào bảng custom_purposes
        $fields = [
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
                'null'       => false,
                'comment'    => 'Số tiền liên quan đến mục đích',
            ],
        ];
        $this->forge->addColumn('custom_purposes', $fields);
    }

    public function down()
    {
        // Xóa cột "amount" khỏi bảng custom_purposes
        $this->forge->dropColumn('custom_purposes', 'amount');
    }
}
