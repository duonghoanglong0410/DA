<?php

namespace App\Models;

use App\Models\BaseModel;

class CashFundCurrenciesModel extends BaseModel
{
    protected $table = 'cash_fund_currencies';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'cash_fund_id', 
        'currency_id', 
        'balance', 
        'created_at', 
        'updated_at'
    ];
    
    /**
     * Lấy thông tin tiền tệ của quỹ (cash fund) cùng với dữ liệu từ bảng currencies.
     *
     * @param int $cashFundId
     * @return array Danh sách bản ghi với các trường: balance, currency_name, abbreviation, symbol, currency_id, ...
     */
    public function getFundCurrencies($cashFundId)
    {
        // Sử dụng query builder trong model (được phép trong model)
        $builder = $this->db->table($this->table . ' as cfc');
        $builder->select('cfc.*, c.name as currency_name, c.abbreviation, c.symbol');
        $builder->join('currencies as c', 'c.id = cfc.currency_id', 'left');
        $builder->where('cfc.cash_fund_id', $cashFundId);
        return $builder->get()->getResultArray();
    }
}
