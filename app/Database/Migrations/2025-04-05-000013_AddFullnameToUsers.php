<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFullnameToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'fullname' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true,
                'comment'    => 'Họ tên'
            ]
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'fullname');
    }
}
