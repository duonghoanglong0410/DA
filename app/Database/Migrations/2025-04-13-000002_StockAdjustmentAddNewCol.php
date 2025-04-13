<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class StockAdjustmentAddNewCol extends Migration
{
    public function up()
    {
        $columns = [
            'old_average_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'after' => 'new_stock',
                'comment' => 'Giá trung bình cũ',
            ],
            'new_average_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'after' => 'old_average_price',
                'comment' => 'Giá trung bình mới',
            ],
        ];

        $this->forge->addColumn('stock_adjustments', $columns);
    }

    public function down()
    {
        $this->forge->dropColumn('stock_adjustments', 'old_average_price');
        $this->forge->dropColumn('stock_adjustments', 'new_average_price');
    }
}