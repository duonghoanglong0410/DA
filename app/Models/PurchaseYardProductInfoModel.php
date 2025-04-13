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
        
        $this->select('purchase_yard_product_info.*, pc.name as category_name, py.yard_name, py.yard_code, currencies.name as currency_name, currencies.symbol');
        $this->join('currencies', 'currencies.id = purchase_yard_product_info.currency_id', 'left');
        $this->join('purchase_yards as py', 'py.id = purchase_yard_product_info.purchase_yard_id', 'left');
        $this->join('product_categories as pc', 'pc.id = purchase_yard_product_info.category_id', 'left');
        $this->whereIn('purchase_yard_id', $yardIds);
        
        $data = $this->findAll();

        foreach ($data as &$row) {
            $row['stock_weight_formatted'] = number_format($row['stock_weight'], 0, '.', ',');
            $row['average_price_formatted'] = number_format($row['average_price'], 0, '.', ',');
            $row['stock_value_formatted'] = number_format($row['stock_weight'] * $row['average_price'], 0, '.', ',');
        }

        return $data;
    }
    
    /**
     * Lấy danh sách loại mặt hàng theo các bãi được phân quyền
     *
     * @param array $yardIds Danh sách ID của bãi
     * @param int|null $currencyId ID của loại tiền tệ (mặc định lấy từ Constants::DEFAULT_CURRENCY_ID)
     * @return array
     */
    public function getProductCategoriesByYardIds($yardIds, $currencyId = null)
    {
        if (empty($yardIds)) {
            return [];
        }
        
        if ($currencyId === null) {
            $currencyId = \App\Constants\Constants::DEFAULT_CURRENCY_ID;
        }
        
        $this->select('product_categories.id, product_categories.name, product_categories.abbreviation, COUNT(DISTINCT purchase_yard_product_info.purchase_yard_id) as yard_count');
        $this->join('product_categories', 'product_categories.id = purchase_yard_product_info.category_id');
        $this->whereIn('purchase_yard_product_info.purchase_yard_id', $yardIds);
        $this->where('purchase_yard_product_info.currency_id', $currencyId);
        $this->groupBy('product_categories.id, product_categories.name, product_categories.abbreviation');
        $this->orderBy('product_categories.name', 'ASC');
        
        return $this->findAll();
    }
    
    /**
     * Lấy danh sách loại tiền tệ được sử dụng trong một bãi
     *
     * @param int $yardId ID của bãi
     * @return array Danh sách các currency_id
     */
    public function getCurrenciesByYardId($yardId)
    {
        $this->select('DISTINCT(currency_id) as currency_id');
        $this->where('purchase_yard_id', $yardId);
        
        return $this->findAll();
    }
} 