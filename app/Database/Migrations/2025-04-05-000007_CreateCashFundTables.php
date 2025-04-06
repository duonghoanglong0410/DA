<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCashFundTables extends Migration
{
    public function up()
    {
        // 1. Create 'cash_funds' table (Thông tin quỹ tiền mặt)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'fund_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => false,
                'comment'    => 'Mã quỹ'
            ],
            'fund_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => false,
                'comment'    => 'Tên quỹ'
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian tạo'
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian cập nhật'
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cash_funds');

        // 2. Create 'cash_fund_currencies' table (Thông tin quỹ tiền tệ theo quỹ tiền mặt)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'cash_fund_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã quỹ'
            ],
            'currency_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã loại tiền tệ'
            ],
            'balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'default'    => 0,
                'comment'    => 'Giá trị tồn quỹ'
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian tạo'
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian cập nhật'
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cash_fund_currencies');
    }

    public function down()
    {
        $this->forge->dropTable('cash_fund_currencies');
        $this->forge->dropTable('cash_funds');
    }
}
