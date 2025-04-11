<?php

namespace App\Models;

class PurchaseYardProductInfoModel extends BaseModel
{
    protected $table = 'purchase_yard_product_info';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'purchase_yard_id', 'category_id', 'currency_id', 'stock_weight', 'average_price', 'created_at', 'updated_at'
    ];
    
    /**
     * Lấy thông tin sản phẩm theo bãi và loại
     *
     * @param int $yardId ID của bãi
     * @param int $categoryId ID của loại sản phẩm
     * @param int|null $currencyId ID của loại tiền tệ (nếu có)
     * @return array|null
     */
    public function getProductInfoByYardAndCategory($yardId, $categoryId, $currencyId = null)
    {
        $this->where('purchase_yard_id', $yardId);
        $this->where('category_id', $categoryId);
        
        if ($currencyId !== null) {
            $this->where('currency_id', $currencyId);
        }
        
        return $this->first();
    }
    
    /**
     * Lấy thông tin sản phẩm theo danh sách bãi
     *
     * @param array $yardIds Danh sách ID của bãi
     * @return array
     */
    public function getInfoByYardIds($yardIds)
    {
        if (empty($yardIds)) {
            return [];
        }
        
        $this->select('purchase_yard_product_info.*, currencies.name as currency_name, currencies.symbol');
        $this->join('currencies', 'currencies.id = purchase_yard_product_info.currency_id', 'left');
        $this->whereIn('purchase_yard_id', $yardIds);
        
        return $this->findAll();
    }
} 