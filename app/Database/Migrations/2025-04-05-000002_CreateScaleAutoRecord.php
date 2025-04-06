<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScaleAutoRecord extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'Msp' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true
            ],
            'Tenkhachhang' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true
            ],
            'Soxe' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true
            ],
            'Loaihang' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true
            ],
            'KLcotai' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true
            ],
            'KLkhongtai' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true
            ],
            'KLhang' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true
            ],
            'Dongia' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true
            ],
            'KLgo' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true
            ],
            'Nguoican' => [
                'type'       => 'int',
                'null'       => true
            ],
            'Sophieu' => [
                'type'       => 'int',
                'null'       => true
            ],
            'Thang' => [
                'type'       => 'int',
                'null'       => true
            ],
            'Nam' => [
                'type'       => 'int',
                'null'       => true
            ],
            'Ghichu' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'phantram' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true
            ],
            'KLtru' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true
            ],
            'Sophieuin' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true
            ],
            'Thanhtien' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true
            ],
            'KLkhongtaiR' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true
            ],
            'KLcotaiR' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true
            ],
            'KLhangR' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true
            ],
            'Tenlaixe' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true
            ],
            'Bangchu' => [
                'type' => 'TEXT',
                'null' => true
            ],
            // Ở dữ liệu mẫu, TNgaycan có dạng số lớn (2.0230305e+07), 
            // bạn có thể dùng DECIMAL(20,4) hoặc VARCHAR nếu muốn lưu raw string
            'TNgaycan' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true
            ],
            // Giờ cân trước/sau có định dạng "dd/mm/yyyy HH:MM:SS", 
            // bạn có thể lưu dạng DATETIME hoặc VARCHAR
            'Giocantruoc' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'Giocansau' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            // Ngày cân có dạng "dd/mm/yyyy", 
            // có thể lưu VARCHAR(10) hoặc DATE (cần convert khi insert)
            'Ngaycan' => [
                'type' => 'DATE',
                'null' => true
            ],
            // solanin, lanin là số lớn ở dạng khoa học, 
            // dùng DECIMAL(20,4) hoặc BIGINT nếu không có phần thập phân
            'solanin' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true
            ],
            'lanin' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'null'       => true
            ],
            'chedo' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('can_tu_dong');
    }

    public function down()
    {
        $this->forge->dropTable('can_tu_dong');
    }
}
