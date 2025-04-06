<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFinancialAndProductTables extends Migration
{
    public function up()
    {
        // 1. Create 'currencies' table (Danh sách loại tiền tệ)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'comment'    => 'Tên loại tiền tệ'
            ],
            'abbreviation' => [
                'type'       => 'CHAR',
                'constraint' => '3',
                'comment'    => 'Tên viết tắt (3 ký tự)'
            ],
            'symbol' => [
                'type'       => 'CHAR',
                'constraint' => '1',
                'comment'    => 'Ký hiệu viết tắt (1 ký tự)'
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
        $this->forge->createTable('currencies');

        // 2. Create 'purchase_yard_currency_funds' table (Thông tin quỹ tiền tệ theo bãi thu mua)
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
        $this->forge->createTable('purchase_yard_currency_funds');

        // 3. Create 'product_categories' table (Danh sách loại mặt hàng)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'comment'    => 'Tên loại mặt hàng'
            ],
            'abbreviation' => [
                'type'       => 'VARCHAR',
                'constraint' => '3',
                'comment'    => 'Tên viết tắt (3 ký tự)'
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
        $this->forge->createTable('product_categories');

        // 4. Create 'purchase_yard_product_info' table (Thông tin loại mặt hàng theo bãi thu mua)
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
            'category_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã loại mặt hàng'
            ],
            'currency_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã loại tiền tệ'
            ],
            'stock_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'default'    => 0,
                'comment'    => 'Khối lượng tồn kho'
            ],
            'average_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'default'    => 0,
                'comment'    => 'Đơn giá bình quân'
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
        $this->forge->createTable('purchase_yard_product_info');

        // Insert initial data into 'currencies'
        $db = \Config\Database::connect();
        $data = [
            [
                'name'         => 'Việt Nam Đồng',
                'abbreviation' => 'VND',
                'symbol'       => '₫',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'name'         => 'Kíp Lào',
                'abbreviation' => 'LAK',
                'symbol'       => '₭',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
        ];
        $db->table('currencies')->insertBatch($data);        

        // Insert initial data into product_categories
        $db = \Config\Database::connect();
        $data = [
            [
                'name'         => 'Sắn khô',
                'abbreviation' => 'SKH',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'name'         => 'Sắn tươi',
                'abbreviation' => 'STU',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
        ];
        $db->table('product_categories')->insertBatch($data);        
    }

    public function down()
    {
        $this->forge->dropTable('purchase_yard_product_info');
        $this->forge->dropTable('product_categories');
        $this->forge->dropTable('purchase_yard_currency_funds');
        $this->forge->dropTable('currencies');
    }
}
