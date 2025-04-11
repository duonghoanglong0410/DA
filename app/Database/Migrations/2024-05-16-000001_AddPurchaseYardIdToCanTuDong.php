<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPurchaseYardIdToCanTuDong extends Migration
{
    public function up()
    {
        $fields = [
            'purchase_yard_id' => [
                'type'       => 'INT',       // Kiểu dữ liệu khớp với purchase_yards.id
                'constraint' => 11,      // Độ dài (nếu cần, thường khớp với PK)
                'unsigned'   => true,    // Đảm bảo là số dương
                'null'       => true,    // Cho phép null ban đầu hoặc nếu không có liên kết
                'comment'    => 'Khóa ngoại, tham chiếu đến bãi thu mua (purchase_yards.id)', // Ghi chú tiếng Việt
                // 'after' => 'cot_khac' // Tùy chọn: thêm cột này sau một cột cụ thể nếu muốn
            ],
        ];
        $this->forge->addColumn('can_tu_dong', $fields);

        // Thêm khóa ngoại sau khi đã thêm cột
        // $this->forge->addForeignKey('purchase_yard_id', 'purchase_yards', 'id', 'CASCADE', 'SET NULL'); 
        // ON UPDATE CASCADE: Nếu id trong purchase_yards thay đổi, cập nhật ở đây
        // ON DELETE SET NULL: Nếu purchase_yard bị xóa, đặt cột này thành NULL
    }

    public function down()
    {
        // Xóa khóa ngoại trước khi xóa cột
        // Tên khóa ngoại thường là: ten_bang_ten_cot_foreign (kiểm tra lại nếu bạn đặt tên khác)
        // $this->forge->dropForeignKey('can_tu_dong', 'can_tu_dong_purchase_yard_id_foreign'); 
        
        $this->forge->dropColumn('can_tu_dong', 'purchase_yard_id');
    }
}
