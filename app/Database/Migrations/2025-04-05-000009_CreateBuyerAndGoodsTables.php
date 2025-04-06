<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBuyerAndGoodsTables extends Migration
{
    public function up()
    {
        // 1. Create 'buyers' table (Thông tin người mua)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'buyer_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'comment'    => 'Tên người mua'
            ],
            'buyer_address' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'comment'    => 'Địa chỉ'
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
        $this->forge->createTable('buyers');

        // 2. Create 'buyer_currency_funds' table (Thông tin quỹ tiền tệ theo người mua)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'buyer_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã người mua'
            ],
            'currency_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã loại tiền tệ'
            ],
            'remaining_debt' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'default'    => 0,
                'comment'    => 'Công nợ phải trả còn lại'
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
        // Thêm UNIQUE cho cột (buyer_id, currency_id)
        $this->forge->addUniqueKey(['buyer_id', 'currency_id']);
        $this->forge->addKey('id', true);
        $this->forge->createTable('buyer_currency_funds');

        // 3. Create 'purchase_yard_goods_receipts' table (Phiếu nhập hàng tại bãi thu mua)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'purchase_yard_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã bãi thu mua'
            ],
            'vehicle_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => 'Biển số xe (không bắt buộc)'
            ],
            'category_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã Loại mặt hàng'
            ],
            'weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Khối lượng'
            ],
            'unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Đơn giá'
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
        $this->forge->createTable('purchase_yard_goods_receipts');
    }

    public function down()
    {
        $this->forge->dropTable('purchase_yard_goods_receipts');
        $this->forge->dropTable('buyer_currency_funds');
        $this->forge->dropTable('buyers');
    }
}
