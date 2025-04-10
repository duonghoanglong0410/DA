<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomsExpenseSummaryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'comment'    => 'Số dư (total_income - total_expense)'
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'Thời gian tạo'
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'comment'    => 'Thời gian cập nhật'
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->createTable('customs_expense_summary');
    }

    public function down()
    {
        $this->forge->dropTable('customs_expense_summary');
    }
} 