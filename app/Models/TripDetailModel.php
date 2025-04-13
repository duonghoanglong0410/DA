<?php namespace App\Models;

use App\Models\BaseModel;

class TripDetailModel extends BaseModel
{
    protected $table      = 'trip_details';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'trip_id',
        'purchase_yard_id',
        'created_by',
        'warehouse_id',
        'purchase_yard_currency_fund_id',
        'export_weight',
        'export_unit_price',
        'export_total_amount',
        'sold_weight',
        'net_weight',
        'selling_unit_price',
        'powder_percentage',
        'freight_fee',
        'customs_cost',
        'other_cost',
        'total_goods_amount',
        'impurity_percentage',
        'impurity_kg',
        'total_freight_amount',
        'created_at',
        'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    /**
     * Lấy tổng khối lượng xuất của một chuyến xe
     * 
     * @param int $tripId ID chuyến xe
     * @return float Tổng khối lượng
     */
    public function getTotalExportWeight($tripId)
    {
        $result = $this->selectSum('export_weight')
                      ->where('trip_id', $tripId)
                      ->first();
        
        return $result['export_weight'] ?? 0;
    }
    
    /**
     * Lấy tổng thành tiền xuất của một chuyến xe
     * 
     * @param int $tripId ID chuyến xe
     * @return float Tổng thành tiền
     */
    public function getTotalExportAmount($tripId)
    {
        $result = $this->selectSum('export_total_amount')
                      ->where('trip_id', $tripId)
                      ->first();
        
        return $result['export_total_amount'] ?? 0;
    }
    
    /**
     * Tính đơn giá bình quân của một chuyến xe
     * 
     * @param int $tripId ID chuyến xe
     * @return float Đơn giá bình quân
     */
    public function getAverageUnitPrice($tripId)
    {
        $totalWeight = $this->getTotalExportWeight($tripId);
        $totalAmount = $this->getTotalExportAmount($tripId);
        
        if ($totalWeight <= 0) {
            return 0;
        }
        
        return $totalAmount / $totalWeight;
    }
    
    /**
     * Lấy danh sách chi tiết của một chuyến xe
     * 
     * @param int $tripId ID chuyến xe
     * @return array Danh sách chi tiết
     */
    public function getDetailsByTripId($tripId)
    {
        return $this->where('trip_id', $tripId)->findAll();
    }
    
    /**
     * Lấy thông tin chi tiết xuất kho theo ID
     * 
     * @param int $id ID của chi tiết xuất kho
     * @return array|null Thông tin chi tiết xuất kho
     */
    public function getDeliveryDetailById($id)
    {
        $builder = $this->db->table($this->table . ' as td');
        $builder->select('
            td.id, td.trip_id, td.purchase_yard_id, td.export_weight, td.export_unit_price, 
            td.export_total_amount, td.created_at, td.created_by,
            py.yard_name, py.yard_code,
            pc.name as category_name,
            c.symbol as currency_symbol,
            u.fullname as creator_name
        ');
        
        $builder->join('trips as t', 't.id = td.trip_id');
        $builder->join('purchase_yards as py', 'py.id = td.purchase_yard_id');
        $builder->join('product_categories as pc', 'pc.id = t.category_id');
        $builder->join('purchase_yard_currency_funds as pycf', 'pycf.id = td.purchase_yard_currency_fund_id', 'left');
        $builder->join('currencies as c', 'c.id = pycf.currency_id', 'left');
        $builder->join('users as u', 'u.id = td.created_by', 'left');
        
        $builder->where('td.id', $id);
        
        $result = $builder->get()->getRowArray();
        
        return $result;
    }
} 