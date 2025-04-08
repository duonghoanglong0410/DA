<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWarehouseAndWarehouseProductCategoryTables extends Migration
{
    public function up()
    {
        // Tạo bảng warehouses để quản lý kho
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính của bảng, tự tăng'
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => 'Tên kho'
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => 'Địa chỉ của kho'
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian tạo bản ghi'
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian cập nhật bản ghi'
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('warehouses', true);

        // Tạo bảng warehouse_product_categories để liên kết kho với các loại mặt hàng
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính của bảng, tự tăng'
            ],
            'warehouse_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Khóa ngoại, tham chiếu đến bảng warehouses'
            ],
            'product_category_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Khóa ngoại, tham chiếu đến bảng product_categories'
            ],
            'stock' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'comment'    => 'Tồn kho'
            ],
            'avg_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'comment'    => 'Đơn giá bình quân'
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian tạo bản ghi'
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian cập nhật bản ghi'
            ],
        ]);
        $this->forge->addKey('id', true);
        // Thêm khóa ngoại để đảm bảo tính liên kết dữ liệu
        // $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
        // $this->forge->addForeignKey('product_category_id', 'product_categories', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['warehouse_id', 'product_category_id']);
        $this->forge->createTable('warehouse_product_categories', true);
    }

    public function down()
    {
        $this->forge->dropTable('warehouse_product_categories', true);
        $this->forge->dropTable('warehouses', true);
    }
}
