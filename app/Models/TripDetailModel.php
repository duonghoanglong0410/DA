<?php namespace App\Models;

use App\Models\BaseModel;

class TripDetailModel extends BaseModel
{
    protected $table      = 'trip_details';
    
    protected $allowedFields = [
        'purchase_yard_id',
        'created_by',
        'trip_id',
        'buyer_currency_fund_id',
        'warehouse_id',
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
} 