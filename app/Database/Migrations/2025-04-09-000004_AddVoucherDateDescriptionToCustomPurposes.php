<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVoucherDateDescriptionToCustomPurposes extends Migration
{
    public function up()
    {
        // Thêm các cột voucher_date (ngày lập phiếu) và description (mô tả phiếu theo dõi) vào bảng custom_purposes
        $fields = [
            'voucher_date' => [
                'type'       => 'DATE',
                'null'       => false,
                'comment'    => 'Ngày lập phiếu'
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Mô tả phiếu theo dõi'
            ],
        ];
        $this->forge->addColumn('custom_purposes', $fields);
    }

    public function down()
    {
        // Loại bỏ cột voucher_date và description khi rollback migration
        $this->forge->dropColumn('custom_purposes', 'voucher_date');
        $this->forge->dropColumn('custom_purposes', 'description');
    }
}
