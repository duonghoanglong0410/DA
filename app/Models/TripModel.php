<?php namespace App\Models;

use App\Models\BaseModel;

class TripModel extends BaseModel
{
    protected $table      = 'trips';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'code',
        'vehicle_number',
        'total_weight',
        'net_weight',
        'clean_weight',
        'product_category_id',
        'factory_id',  // Liên kết đến buyers.id
        'purchase_yard_currency_fund_id',  // Dù tên cột này không liên quan trực tiếp đến buyers, nhưng dùng cho kiểm tra tham chiếu
        'created_at'
    ];
        
    // Đếm số bản ghi trong trips có purchase_yard_currency_fund_id bằng $fundId
    public function countByCurrencyFundId($fundId)
    {
        return $this->where('purchase_yard_currency_fund_id', $fundId)->countAllResults();
    }
}
