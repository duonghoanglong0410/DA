<?php

namespace App\Models;

use App\Models\BaseModel;

class PurchaseYardsModel extends BaseModel
{
    protected $table = 'purchase_yards';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'yard_code',      // Mã bãi thu mua
        'yard_name',      // Tên bãi
        'client_api_key', // Client API Key
        'status',         // Trạng thái (1: hoạt động, 0: không hoạt động)
        'created_at',
        'updated_at'
    ];
    
    /**
     * Lấy mã bãi tiếp theo theo định dạng Yxxxx.
     *
     * @return string Mã bãi mới, ví dụ: Y0001, Y0002,...
     */
    public function getNextYardCode()
    {
        $builder = $this->db->table($this->table);
        $builder->select('yard_code');
        $builder->orderBy('yard_code', 'DESC');
        $builder->limit(1);
        $row = $builder->get()->getRow();
        
        if ($row && isset($row->yard_code)) {
            $currentNumber = intval(substr($row->yard_code, 1));
            $newNumber = $currentNumber + 1;
        } else {
            $newNumber = 1;
        }
        return 'B' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
