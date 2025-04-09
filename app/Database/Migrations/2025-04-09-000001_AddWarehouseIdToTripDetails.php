<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWarehouseIdToTripDetails extends Migration
{
    public function up()
    {
        // Thêm cột warehouse_id vào bảng trip_details, cho phép null (nếu cần)
        $this->forge->addColumn('trip_details', [
            'warehouse_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'buyer_currency_fund_id', // Bạn có thể điều chỉnh vị trí theo yêu cầu
                'comment'    => 'Mã của kho đã xuất hàng', 
            ],
        ]);

        // Thêm khóa ngoại cho cột warehouse_id tham chiếu tới bảng warehouses
        // $this->forge->addForeignKey('warehouse_id', 'warehouses', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        // Thả khóa ngoại trước, theo quy tắc tên tự động của CI4 thường là trip_details_warehouse_id_foreign
        $this->forge->dropForeignKey('trip_details', 'trip_details_warehouse_id_foreign');
        // Xóa cột warehouse_id
        $this->forge->dropColumn('trip_details', 'warehouse_id');
    }
}
