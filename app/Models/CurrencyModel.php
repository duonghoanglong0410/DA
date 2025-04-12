<?php

namespace App\Models;

use App\Models\BaseModel;

class CurrencyModel extends BaseModel
{
    protected $table = 'currencies';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',           // Ví dụ: "Việt Nam Đồng"
        'abbreviation',   // Ví dụ: "VND"
        'symbol',         // Ví dụ: "₫"
        'created_at',
        'updated_at'
    ];
    
    /**
     * Lấy danh sách tiền tệ được sử dụng tại một bãi cụ thể
     * 
     * @param int $yardId ID của bãi cần lấy danh sách tiền tệ
     * @return array Danh sách tiền tệ của bãi
     */
    public function getCurrenciesByYardId($yardId)
    {
        // Lấy danh sách tiền tệ từ purchase_yard_product_info
        $result = $this
            ->select('currencies.*')
            ->join('purchase_yard_product_info', 'purchase_yard_product_info.currency_id = currencies.id', 'inner')
            ->where('purchase_yard_product_info.purchase_yard_id', $yardId)
            ->groupBy('currencies.id')
            ->findAll();
            
        // Nếu không có tiền tệ nào được tìm thấy, trả về một mảng chứa tiền tệ mặc định (VND)
        if (empty($result)) {
            // Lấy tiền tệ mặc định (VND)
            $defaultCurrency = $this->where('abbreviation', 'VND')->first();
            if ($defaultCurrency) {
                $result = [$defaultCurrency];
            }
        }
        
        return $result;
    }
}
