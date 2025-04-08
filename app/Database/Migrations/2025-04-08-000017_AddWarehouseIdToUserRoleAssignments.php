<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWarehouseIdToUserRoleAssignments extends Migration
{
    public function up()
    {
        // Thêm cột warehouse_id vào bảng user_role_assignments, cho phép null (nếu cần)
        $this->forge->addColumn('user_role_assignments', [
            'warehouse_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'cash_fund_id', // thêm sau cột cash_fund_id, tùy chỉnh theo mong muốn
            ],
        ]);

        // Thêm khóa ngoại cho cột warehouse_id tham chiếu tới warehouses.id, 
        // sử dụng cascade cho cả hành động delete và update
        // $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        // Thả khóa ngoại trước, tên khóa được tự động tạo theo quy tắc của CI4
        $this->forge->dropForeignKey('user_role_assignments', 'user_role_assignments_warehouse_id_foreign');
        // Sau đó xóa cột warehouse_id
        $this->forge->dropColumn('user_role_assignments', 'warehouse_id');
    }
}
