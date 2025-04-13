<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReviewedByToDeliveryAdjustments extends Migration
{
    public function up()
    {
        $this->forge->addColumn('delivery_adjustments', [
            'reviewed_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'status',
                'comment' => 'Người duyệt phiếu điều chỉnh'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('delivery_adjustments', 'reviewed_by');
    }
}