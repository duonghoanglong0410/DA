<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserRoleAssignmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
            ],
            'role_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
            ],
            // purchase_yard_id: nếu role phân theo bãi, trường này lưu giá trị bãi; nếu không, để NULL.
            'purchase_yard_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'cash_fund_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        // Bạn có thể đặt composite unique key để đảm bảo không có bản ghi trùng lặp
        $this->forge->addUniqueKey(['user_id', 'role_id', 'purchase_yard_id']);
        $this->forge->createTable('user_role_assignments');
    }

    public function down()
    {
        $this->forge->dropTable('user_role_assignments');
    }
}
