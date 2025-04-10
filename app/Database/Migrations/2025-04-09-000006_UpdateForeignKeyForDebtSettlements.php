<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateForeignKeyForDebtSettlements extends Migration
{
    public function up()
    {
        // Drop ràng buộc khóa ngoại cũ trên cột followup_voucher_id (tham chiếu đến custom_cash_journals.id)
        // Lưu ý: Tên ràng buộc khóa ngoại được giả định là "fk_debt_settlements_followup_voucher".
        $this->forge->dropForeignKey('custom_debt_settlements', 'fk_debt_settlements_followup_voucher');
        
        // Thêm ràng buộc khóa ngoại mới: followup_voucher_id tham chiếu đến custom_purposes.id
        $this->forge->addForeignKey('followup_voucher_id', 'custom_purposes', 'id', 'CASCADE', 'CASCADE', 'fk_debt_settlements_followup_voucher');
    }

    public function down()
    {
        // Đảo ngược: drop ràng buộc khóa ngoại hiện tại và phục hồi ràng buộc cũ
        $this->forge->dropForeignKey('custom_debt_settlements', 'fk_debt_settlements_followup_voucher');
        $this->forge->addForeignKey('followup_voucher_id', 'custom_cash_journals', 'id', 'CASCADE', 'CASCADE', 'fk_debt_settlements_followup_voucher');
    }
}
