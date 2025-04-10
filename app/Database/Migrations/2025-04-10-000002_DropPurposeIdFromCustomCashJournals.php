<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropPurposeIdFromCustomCashJournals extends Migration
{
    public function up()
    {

        // Bỏ cột purpose_id khỏi bảng custom_cash_journals
        $this->forge->dropColumn('custom_cash_journals', 'purpose_id');
    }

    public function down()
    {
        // Khôi phục lại cột purpose_id khi rollback migration.
        // Lưu ý: Kiểu dữ liệu, ràng buộc và comment có thể được thay đổi theo yêu cầu dự án.
        $fields = [
            'purpose_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Tham chiếu đến custom_purposes.id',
            ],
        ];
        $this->forge->addColumn('custom_cash_journals', $fields);
    }
}
