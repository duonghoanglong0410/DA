<?php namespace App\Models;

use App\Models\BaseModel;

class PurchaseYardCurrencyFundModel extends BaseModel
{
    protected $table      = 'purchase_yard_currency_funds';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'purchase_yard_id',
        'currency_id',
        'balance',
        'created_at',
        'updated_at'
    ];

    // Lấy danh sách quỹ tiền của kho bãi theo purchase_yard_id
    public function getFundCurrencies($yardId)
    {
        $builder = $this->db->table($this->table . ' as pycf');
        $builder->select('pycf.*, c.name as currency_name, c.abbreviation, c.symbol');
        $builder->join('currencies as c', 'c.id = pycf.currency_id', 'left');
        $builder->where('pycf.yard_id', $yardId);
        return $builder->get()->getResultArray();
    }
    
    /**
     * Kiểm tra xem bản ghi purchase_yard_currency_funds có được tham chiếu trong bảng yard_vouchers hay không.
     * Giả sử bảng yard_vouchers có các cột: sending_yard_currency_fund_id và receiving_yard_currency_fund_id.
     *
     * @param int $pycfId
     * @return bool True nếu có tham chiếu, False nếu không.
     */
    public function isReferenced($pycfId)
    {
        $builder = $this->db->table('yard_vouchers');
        $builder->where('sending_yard_currency_fund_id', $pycfId);
        $builder->orWhere('receiving_yard_currency_fund_id', $pycfId);
        $count = $builder->countAllResults();
        return ($count > 0);
    }
}
