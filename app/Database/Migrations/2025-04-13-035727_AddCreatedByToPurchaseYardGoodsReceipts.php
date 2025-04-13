<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCreatedByToPurchaseYardGoodsReceipts extends Migration
{
    public function up()
    {
        // Thêm cột created_by
        $this->forge->addColumn('purchase_yard_goods_receipts', [
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'unit_price'
            ],
        ]);

        // Thêm khóa ngoại bằng cách thực thi câu lệnh SQL trực tiếp
        // Không sử dụng processIndexes() để tránh lỗi
        // $this->db->query('ALTER TABLE `purchase_yard_goods_receipts` ADD INDEX `idx_purchase_yard_goods_receipts_created_by` (`created_by`)');
        // $this->db->query('ALTER TABLE `purchase_yard_goods_receipts` ADD CONSTRAINT `fk_purchase_yard_goods_receipts_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        // Xóa khóa ngoại
        // $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        // $this->db->query('ALTER TABLE `purchase_yard_goods_receipts` DROP FOREIGN KEY `fk_purchase_yard_goods_receipts_created_by`');
        // $this->db->query('ALTER TABLE `purchase_yard_goods_receipts` DROP INDEX `idx_purchase_yard_goods_receipts_created_by`');
        // $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        
        // Xóa cột
        $this->forge->dropColumn('purchase_yard_goods_receipts', 'created_by');
    }
}
