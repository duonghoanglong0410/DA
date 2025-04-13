<?php

namespace App\Models;

use CodeIgniter\Model;

class StockAdjustmentModel extends BaseModel
{
    protected $table = 'stock_adjustments';
    protected $allowedFields = ['warehouse_id', 'purchase_yard_product_info_id', 'old_stock', 'new_stock', 'old_average_price', 'new_average_price', 'created_by', 'created_at', 'updated_at'];
    
    
}

