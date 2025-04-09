<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomTables extends Migration
{
    public function up()
    {
        // Tạo bảng custom_purposes
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'purpose_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'comment'    => 'Tên mục đích',
            ],
            'created_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Người lập (tham chiếu đến table users)',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian tạo bản ghi',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian cập nhật bản ghi',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('custom_purposes', true);

        // Thêm khóa ngoại cho custom_purposes.created_by tham chiếu đến users.id
        // $this->db->query('ALTER TABLE custom_purposes ADD CONSTRAINT fk_custom_purposes_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE');

        // Tạo bảng custom_cash_journals (lưu trữ phiếu thu, phiếu chi và phiếu theo dõi)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'created_date' => [
                'type'    => 'DATE',
                'null'    => false,
                'comment' => 'Ngày lập phiếu',
            ],
            'transaction_type' => [
                'type'    => "ENUM('thu','chi')",
                'null'    => false,
                'comment' => 'Loại giao dịch: thu (phiếu thu tiền), chi (phiếu chi tiền)',
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'comment'    => 'Số tiền của giao dịch',
            ],
            'description' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Mô tả giao dịch',
            ],
            'purpose_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Khóa ngoại tham chiếu đến bảng custom_purposes (nếu có)',
            ],
            'created_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Người lập (tham chiếu đến table users)',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian tạo phiếu',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian cập nhật phiếu',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('custom_cash_journals', true);

        // Thêm khóa ngoại cho custom_cash_journals.created_by tham chiếu đến users.id
        $this->db->query('ALTER TABLE custom_cash_journals ADD CONSTRAINT fk_custom_cash_journals_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE');

        // Thêm khóa ngoại cho custom_cash_journals.purpose_id tham chiếu đến custom_purposes.id (cho các phiếu có mục đích)
        $this->db->query('ALTER TABLE custom_cash_journals ADD CONSTRAINT fk_custom_cash_journals_purpose_id FOREIGN KEY (purpose_id) REFERENCES custom_purposes(id) ON DELETE SET NULL ON UPDATE CASCADE');

        // Tạo bảng custom_debt_settlements (liên kết thanh toán công nợ)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'payment_voucher_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Khóa ngoại tham chiếu đến custom_cash_journals (giao dịch chi)',
            ],
            'followup_voucher_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Khóa ngoại tham chiếu đến custom_cash_journals (giao dịch theo dõi)',
            ],
            'settlement_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'comment'    => 'Số tiền thanh toán áp dụng cho phiếu theo dõi',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian tạo bản ghi',
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'comment' => 'Thời gian cập nhật bản ghi',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('custom_debt_settlements', true);

        // Thêm khóa ngoại cho custom_debt_settlements.payment_voucher_id tham chiếu đến custom_cash_journals.id (giao dịch chi)
        $this->db->query('ALTER TABLE custom_debt_settlements ADD CONSTRAINT fk_debt_settlements_payment_voucher FOREIGN KEY (payment_voucher_id) REFERENCES custom_cash_journals(id) ON DELETE CASCADE ON UPDATE CASCADE');

        // Thêm khóa ngoại cho custom_debt_settlements.followup_voucher_id tham chiếu đến custom_cash_journals.id (giao dịch theo dõi)
        $this->db->query('ALTER TABLE custom_debt_settlements ADD CONSTRAINT fk_debt_settlements_followup_voucher FOREIGN KEY (followup_voucher_id) REFERENCES custom_cash_journals(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down()
    {
        // Thứ tự xóa bảng phải theo thứ tự phụ thuộc ngược lại
        $this->forge->dropTable('custom_debt_settlements', true);
        $this->forge->dropTable('custom_cash_journals', true);
        $this->forge->dropTable('custom_purposes', true);
    }
}
