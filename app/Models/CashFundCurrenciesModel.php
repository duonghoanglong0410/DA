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
     * Kiểm tra xem bản ghi cash_fund_currency có được tham chiếu trong bảng cash_vouchers hay không.
     *
     * @param int $cfId ID của cash_fund_currencies
     * @return bool True nếu có tham chiếu, False nếu không.
     */
    public function isReferenced($cfId)
    {
        $builder = $this->db->table('cash_vouchers');
        $builder->where('sending_currency_fund_id', $cfId);
        $builder->orWhere('receiving_currency_fund_id', $cfId);
        $count = $builder->countAllResults();
        return ($count > 0);
    }
    
    /**
     * Lấy thông tin tiền tệ của quỹ, kèm dữ liệu từ bảng currencies.
     *
     * @param int $cashFundId
     * @return array
     */
    public function getFundCurrencies($cashFundId)
    {
        $builder = $this->db->table($this->table . ' as cfc');
        $builder->select('cfc.*, c.name as currency_name, c.abbreviation, c.symbol');
        $builder->join('currencies as c', 'c.id = cfc.currency_id', 'left');
        $builder->where('cfc.cash_fund_id', $cashFundId);
        return $builder->get()->getResultArray();
    }
}
