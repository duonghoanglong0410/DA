<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExternalPurchaseAndVoucherTables extends Migration
{
    public function up()
    {
        /*
         * 1. Table: external_purchase_items (Thông tin mặt hàng mua ngoài)
         *    - item_code: Mã mặt hàng
         *    - unit: Đơn vị tính (không bắt buộc)
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INT', 
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính'
            ],
            'item_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'comment'    => 'Mã mặt hàng'
            ],
            'unit' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'comment'    => 'Đơn vị tính (không bắt buộc)'
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
            ]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('external_purchase_items');

        /*
         * 2. Table: cash_vouchers (Thông tin phiếu thu/chi tiền)
         *    - sending_currency_fund_id: Mã quỹ tiền tệ bên gửi (không bắt buộc)
         *    - created_by: Người lập
         *    - creation_date: Ngày lập
         *    - amount: Số tiền
         *    - external_item_id: Mã mặt hàng (không bắt buộc)
         *    - unit: Đơn vị tính (không bắt buộc)
         *    - purchase_unit_price: Đơn giá hàng mua (không bắt buộc)
         *    - purchase_quantity: Số lượng hàng mua (không bắt buộc)
         *    - sent_total: Thành tiền gửi
         *    - description: Mô tả
         *    - receiving_currency_fund_id: Mã quỹ tiền tệ bên nhận (không bắt buộc)
         *    - confirmed_by: Người xác nhận nhận tiền (không bắt buộc)
         *    - status: Trạng thái
         *    - exchange_rate: Tỷ giá (mặc định null)
         *    - received_total: Thành tiền nhận
         *    - approved_by: Người phê duyệt
         *    - approval_status: Trạng thái phê duyệt
         *    - voucher_type: Loại phiếu (101: Phiếu chi ngoài, 102: Phiếu chi nội bộ, 103: Phiếu chi tiền cước, 104: Phiếu chi hải quan, 105: Phiếu chi chi phí khác, 201: Thu công nợ khách hàng, 202: Thu khác, 301: Phiếu đổi tiền)
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INT', 
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính'
            ],
            'sending_currency_fund_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Mã quỹ tiền tệ bên gửi (không bắt buộc)'
            ],
            'created_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Người lập'
            ],
            'creation_date' => [
                'type'    => 'DATE',
                'comment' => 'Ngày lập'
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Số tiền'
            ],
            'external_item_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Mã mặt hàng (không bắt buộc)'
            ],
            'unit' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'comment'    => 'Đơn vị tính (không bắt buộc)'
            ],
            'purchase_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true,
                'comment'    => 'Đơn giá hàng mua (không bắt buộc)'
            ],
            'purchase_quantity' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true,
                'comment'    => 'Số lượng hàng mua (không bắt buộc)'
            ],
            'sent_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Thành tiền gửi'
            ],
            'description' => [
                'type'    => 'TEXT',
                'comment' => 'Mô tả'
            ],
            'receiving_currency_fund_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Tham chiếu đến CASH_FUND_CURRENCIES, BUYER_CURRENCY_FUNDS hoặc PURCHASE_YARD_CURRENCY_FUNDS'
            ],
            'confirmed_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Người xác nhận nhận tiền (không bắt buộc)'
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => '1',
                'comment'    => 'Trạng thái'
            ],
            'exchange_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true,
                'comment'    => 'Tỷ giá (mặc định null)'
            ],
            'received_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Thành tiền nhận'
            ],
            'approved_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Người phê duyệt'
            ],
            'approval_status' => [
                'type'       => 'TINYINT',
                'constraint' => '1',
                'comment'    => 'Trạng thái phê duyệt'
            ],
            'voucher_type' => [
                'type'       => 'INT',
                'comment'    => 'Loại phiếu: 101,102,103,104,105,201,202,301'
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
        $this->forge->createTable('cash_vouchers');

        /*
         * 3. Table: voucher_details (Chi tiết phiếu thu/chi công nợ khách hàng)
         *    - voucher_id: Mã phiếu thu/chi
         *    - trip_id: Mã số chuyến xe
         *    - allocated_debt: Phân bổ công nợ đã trả
         *    - remaining_trip_debt: Công nợ còn lại của chuyến
         *    - freight_fee: Tiền cước
         *    - remaining_freight_fee: Tiền cước còn lại
         *    - customs_fee: Phí hải quan
         *    - remaining_customs_fee: Phí hải quan còn lại
         *    - other_cost: Chi phí khác
         *    - remaining_other_cost: Chi phí khác còn lại
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INT', 
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'voucher_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã phiếu thu/chi'
            ],
            'trip_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã số chuyến xe'
            ],
            'allocated_debt' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Phân bổ công nợ đã trả'
            ],
            'remaining_trip_debt' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Công nợ còn lại của chuyến'
            ],
            'freight_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tiền cước'
            ],
            'remaining_freight_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tiền cước còn lại'
            ],
            'customs_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Phí hải quan'
            ],
            'remaining_customs_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Phí hải quan còn lại'
            ],
            'other_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Chi phí khác'
            ],
            'remaining_other_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Chi phí khác còn lại'
            ],
            'created_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Người lập'
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
        $this->forge->createTable('voucher_details');
    }

    public function down()
    {
        $this->forge->dropTable('voucher_details');
        $this->forge->dropTable('cash_vouchers');
        $this->forge->dropTable('external_purchase_items');
    }
}
