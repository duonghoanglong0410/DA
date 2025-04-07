<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTargetTypeToRoles extends Migration
{
    public function up()
    {
        // Thêm cột target_type kiểu TINYINT, default 0 vào bảng roles
        $fields = [
            'target_type' => [
                'type'       => 'TINYINT',
                'constraint' => '1',
                'default'    => 0,
                'comment'    => 'Loại đối tượng mà role áp dụng (0: mặc định, 1: đối tượng đặc biệt)'
            ]
        ];
        $this->forge->addColumn('roles', $fields);
        
        // Cập nhật target_type = 1 cho các role có id 1,2,4,5,8
        $this->db->query("UPDATE roles SET target_type = 1 WHERE id IN (1,2,4,5,8)");
        $this->db->query("UPDATE roles SET target_type = 2 WHERE id IN (7)");
    }

    public function down()
    {
        // Xoá cột target_type
        $this->forge->dropColumn('roles', 'target_type');
    }
}
