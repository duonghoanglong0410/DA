<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsMainMenuAndIconClassToPermissions extends Migration
{
    public function up()
    {
        $fields = [
            'is_main_menu' => [
                'type'       => 'TINYINT',
                'constraint' => '1',
                'default'    => 0,
                'comment'    => 'Có hiển thị trong menu chính (1: Có, 0: Không)'
            ],
            'icon_class' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => 'Lớp icon Font Awesome'
            ],
        ];
        $this->forge->addColumn('permissions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('permissions', 'is_main_menu');
        $this->forge->dropColumn('permissions', 'icon_class');
    }
}
