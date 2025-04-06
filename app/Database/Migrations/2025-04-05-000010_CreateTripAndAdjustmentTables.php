<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTripAndAdjustmentTables extends Migration
{
    public function up()
    {
        /*
         * 1. Table: trips (Thông tin chuyến xe)
         * - factory_id: tham chiếu đến BUYERS
         * - purchase_yard_currency_fund_id: tham chiếu đến PURCHASE_YARD_CURRENCY_FUNDS
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INT', 
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'trip_code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'comment'    => 'Mã số chuyến xe'
            ],
            'receipt_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'comment'    => 'Số phiếu'
            ],
            'vehicle_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'comment'    => 'Biển số xe'
            ],
            'avg_export_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Đơn giá xuất bình quân'
            ],
            'export_total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Thành tiền xuất'
            ],
            'total_export_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tổng trọng lượng xuất'
            ],
            'category_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã Loại mặt hàng'
            ],
            'purchase_yard_weighing_date' => [
                'type'    => 'DATE',
                'comment' => 'Ngày cân tại bãi thu mua'
            ],
            'factory_weighing_date' => [
                'type'    => 'DATE',
                'comment' => 'Ngày cân tại nhà máy'
            ],
            'factory_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã Nhà máy'
            ],
            'purchase_yard_currency_fund_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã quỹ tiền tệ theo người mua'
            ],
            'document_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => 'Số chứng từ (không bắt buộc)'
            ],
            'sold_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Khối lượng hàng bán'
            ],
            'net_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Khối lượng sạch'
            ],
            'selling_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Đơn giá bán'
            ],
            'powder_percentage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'comment'    => 'Độ bột'
            ],
            'freight_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tiền cước'
            ],
            'customs_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Chi phí hải quan'
            ],
            'other_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Chi phí khác'
            ],
            'total_goods_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tổng tiền hàng'
            ],
            'impurity_percentage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'comment'    => 'Tỷ lệ tạp chất %'
            ],
            'impurity_kg' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tỷ lệ tạp chất kg'
            ],
            'total_freight_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tổng tiền cước'
            ],
            'remaining_goods_debt' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Công nợ tiền hàng phải trả còn lại'
            ],
            'remaining_freight_debt' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Công nợ tiền cước phải trả còn lại'
            ],
            'remaining_customs_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Chi phí hải quan phải trả còn lại'
            ],
            'remaining_other_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Chi phí khác phải trả còn lại'
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
        $this->forge->addKey('id', true);
        $this->forge->createTable('trips');

        /*
         * 2. Table: trip_details (Chi tiết mỗi chuyến xe)
         * - purchase_yard_id: tham chiếu đến PURCHASE_YARDS
         * - buyer_currency_fund_id: tham chiếu đến BUYER_CURRENCY_FUNDS
         * - created_by: tham chiếu đến USERS
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INT', 
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'purchase_yard_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã bãi thu mua'
            ],
            'created_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Người lập'
            ],
            'trip_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã số chuyến xe'
            ],
            'buyer_currency_fund_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã quỹ tiền tệ theo bãi'
            ],
            'export_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Khối lượng xuất'
            ],
            'export_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Đơn giá xuất'
            ],
            'export_total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Thành tiền xuất'
            ],
            'sold_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Khối lượng hàng bán'
            ],
            'net_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Khối lượng sạch'
            ],
            'selling_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Đơn giá bán'
            ],
            'powder_percentage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'comment'    => 'Độ bột'
            ],
            'freight_fee' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tiền cước'
            ],
            'customs_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Chi phí hải quan'
            ],
            'other_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Chi phí khác'
            ],
            'total_goods_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tổng tiền hàng'
            ],
            'impurity_percentage' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'comment'    => 'Tỷ lệ tạp chất %'
            ],
            'impurity_kg' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tỷ lệ tạp chất kg'
            ],
            'total_freight_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tổng tiền cước'
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
        $this->forge->addKey('id', true);
        $this->forge->createTable('trip_details');

        /*
         * 3. Table: receipt_adjustments (Phiếu điều chỉnh phiếu cân nhập hàng)
         * - Thêm created_by và reviewed_by tham chiếu đến USERS
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INT', 
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'receipt_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã số phiếu nhập hàng'
            ],
            'old_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Khối lượng cũ'
            ],
            'old_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Đơn giá cũ'
            ],
            'new_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Khối lượng mới'
            ],
            'new_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Đơn giá mới'
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => '1',
                'comment'    => 'Trạng thái'
            ],
            'created_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Người lập'
            ],
            'reviewed_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Người duyệt'
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
        $this->forge->addKey('id', true);
        $this->forge->createTable('receipt_adjustments');

        /*
         * 4. Table: delivery_adjustments (Phiếu điều chỉnh phiếu cân xuất hàng)
         * - delivery_id tham chiếu đến TRIP_DETAILS
         * - Thêm created_by tham chiếu đến USERS
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INT', 
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'delivery_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã số phiếu xuất hàng tại bãi'
            ],
            'old_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Khối lượng cũ'
            ],
            'old_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Đơn giá cũ'
            ],
            'new_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Khối lượng mới'
            ],
            'new_unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Đơn giá mới'
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => '1',
                'comment'    => 'Trạng thái'
            ],
            'created_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Người lập'
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
        $this->forge->addKey('id', true);
        $this->forge->createTable('delivery_adjustments');

        /*
         * 5. Table: stock_adjustments (Phiếu điều chỉnh tồn kho)
         * - warehouse_id tham chiếu đến PURCHASE_YARDS
         * - created_by tham chiếu đến USERS
         */
        $this->forge->addField([
            'id' => [
                'type'           => 'INT', 
                'unsigned'       => true,
                'auto_increment' => true,
                'comment'        => 'Khóa chính tự động tăng'
            ],
            'warehouse_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã kho'
            ],
            'purchase_yard_product_info_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Mã mặt hàng theo bãi thu mua'
            ],
            'created_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'comment'    => 'Người lập'
            ],
            'old_stock' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tồn kho cũ'
            ],
            'new_stock' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,4',
                'comment'    => 'Tồn kho mới'
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
        $this->forge->addKey('id', true);
        $this->forge->createTable('stock_adjustments');
    }

    public function down()
    {
        $this->forge->dropTable('stock_adjustments');
        $this->forge->dropTable('delivery_adjustments');
        $this->forge->dropTable('receipt_adjustments');
        $this->forge->dropTable('trip_details');
        $this->forge->dropTable('trips');
    }
}
