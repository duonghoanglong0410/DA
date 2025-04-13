<?php namespace App\Models;

use App\Constants\VoucherTypes;
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
        $builder->select('pycf.*, py.yard_name, py.yard_code, c.name as currency_name, c.abbreviation, c.symbol');
        $builder->join('currencies as c', 'c.id = pycf.currency_id', 'left');
        $builder->join('purchase_yards as py', 'py.id = pycf.purchase_yard_id', 'left');
        if (is_array($yardId)) {
            $builder->whereIn('pycf.purchase_yard_id', $yardId);
        } else {
            $builder->where('pycf.purchase_yard_id', $yardId);
        }
        $data = $builder->get()->getResultArray();
        foreach ($data as &$row) {
            $row['balance_formatted'] = number_format($row['balance'], 0, '.', ',');
        }
        return $data;
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
        $builder = $this->db->table('cash_vouchers');
        $builder->where('sending_currency_fund_id', $pycfId);
        $builder->orWhere('receiving_currency_fund_id', $pycfId);
        $count = $builder->countAllResults();
        return ($count > 0);

        $builder = $this->db->table('cash_vouchers');
        $builder->groupStart()
                    ->groupStart()
                        ->where('sending_currency_fund_id', $pycfId)
                        ->where("voucher_type BETWEEN " . VoucherTypes::EXTERNAL_EXPENSE . " AND " . VoucherTypes::INTERNAL_EXPENSE . "", null, false)
                    ->groupEnd()
                    ->orGroupStart()
                        ->where('sending_currency_fund_id', $pycfId)
                        ->where("voucher_type", VoucherTypes::OTHER_EXPENSE)
                    ->groupEnd()
                    ->orGroupStart()
                        ->where('sending_currency_fund_id', $pycfId)
                        ->where('voucher_type', 301)
                    ->groupEnd()
                    ->orGroupStart()
                        ->where('receiving_currency_fund_id', $pycfId)
                        ->where("voucher_type", VoucherTypes::CUSTOMER_COLLECTION)
                    ->groupEnd()
                ->groupEnd();
        $count = $builder->countAllResults();
        return ($count > 0);        
    }
}
