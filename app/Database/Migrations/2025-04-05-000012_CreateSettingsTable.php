<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => 'Khoá cấu hình (mã cấu hình hệ thống)'
            ],
            'value' => [
                'type'    => 'LONGTEXT',
                'null'    => true,
                'comment' => 'Giá trị cấu hình'
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
        $this->forge->addKey('key', true);
        $this->forge->createTable('settings');
    }

    public function down()
    {
        $this->forge->dropTable('settings');
    }
}
