<?php namespace App\Models;

use App\Models\BaseModel;

class DeliveryAdjustmentModel extends BaseModel
{
    protected $table      = 'delivery_adjustments';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'delivery_id',
        'old_weight',
        'old_unit_price',
        'new_weight',
        'new_unit_price',
        'status',
        'reason',
        'approved_by',
        'created_by',
        'created_at',
        'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    /**
     * Get pending adjustments for approval
     *
     * @return array
     */
    public function getAdjustmentsByYardIds($yardIds = [])
    {
        return $this->select('
                delivery_adjustments.*,
                td.purchase_yard_id,
                td.export_weight, td.export_unit_price,
                t.trip_code, t.vehicle_number,
                py.yard_name, py.yard_code,
                pc.name as category_name,
                c.symbol as currency_symbol,
                u.fullname as creator_name
            ')
            ->join('trip_details as td', 'td.id = delivery_adjustments.delivery_id')
            ->join('trips as t', 't.id = td.trip_id')
            ->join('purchase_yards as py', 'py.id = td.purchase_yard_id')
            ->join('product_categories as pc', 'pc.id = t.category_id')
            ->join('purchase_yard_currency_funds as tycf', 'tycf.id = td.purchase_yard_currency_fund_id', 'left')
            ->join('currencies as c', 'c.id = tycf.currency_id', 'left')
            ->join('users as u', 'u.id = delivery_adjustments.created_by', 'left')
            ->where('delivery_adjustments.status', 0) // Pending status
            ->whereIn('td.purchase_yard_id', $yardIds)
            ->orderBy('delivery_adjustments.created_at', 'DESC')
            ->findAll();
    }
    
    /**
     * Lấy danh sách phiếu điều chỉnh xuất kho chờ duyệt theo bãi
     * 
     * Hàm này lấy danh sách các phiếu điều chỉnh xuất kho đang chờ duyệt
     * dựa trên danh sách ID bãi được phân quyền cho người dùng.
     * Kết quả được sắp xếp theo thời gian tạo mới nhất.
     *
     * @param array $yardIds Danh sách ID bãi mà người dùng có quyền truy cập
     * @return array Danh sách phiếu điều chỉnh đang chờ duyệt
     */
    public function getPendingAdjustmentsByYardIds($yardIds = [])
    {
        $builder = $this->select('
                delivery_adjustments.*,
                td.export_weight, td.export_unit_price, td.purchase_yard_id,
                t.trip_code, t.vehicle_number, t.category_id,
                py.yard_name, py.yard_code,
                pc.name as category_name,
                c.symbol as currency_symbol, c.abbreviation as currency_code,
                u.fullname as creator_name,
                (delivery_adjustments.new_weight - td.export_weight) as weight_difference,
                (delivery_adjustments.new_unit_price - td.export_unit_price) as price_difference
            ')
            ->join('trip_details as td', 'td.id = delivery_adjustments.delivery_id')
            ->join('trips as t', 't.id = td.trip_id')
            ->join('purchase_yards as py', 'py.id = td.purchase_yard_id')
            ->join('product_categories as pc', 'pc.id = t.category_id')
            ->join('purchase_yard_currency_funds as pycf', 'pycf.id = td.purchase_yard_currency_fund_id', 'left')
            ->join('currencies as c', 'c.id = pycf.currency_id', 'left')
            ->join('users as u', 'u.id = delivery_adjustments.created_by', 'left')
            ->where('delivery_adjustments.status', 0); // Trạng thái chờ duyệt
            
        // Lọc theo danh sách bãi được phân quyền
        if (!empty($yardIds)) {
            $builder->whereIn('td.purchase_yard_id', $yardIds);
        }
        
        // Sắp xếp theo thời gian tạo mới nhất
        return $builder->orderBy('delivery_adjustments.created_at', 'DESC')->findAll();
    }
    
    /**
     * Get adjustment by ID with all related details
     *
     * @param int $id
     * @return array|null
     */
    public function getAdjustmentById($id)
    {
        return $this->select('
                delivery_adjustments.*,
                td.export_weight, td.export_unit_price, td.purchase_yard_id,
                t.trip_code, t.vehicle_number, t.category_id,
                py.yard_name, py.yard_code,
                pc.name as category_name,
                c.symbol as currency_symbol,
                u.fullname as creator_name,
                ua.fullname as approver_name
            ')
            ->join('trip_details as td', 'td.id = delivery_adjustments.delivery_id')
            ->join('trips as t', 't.id = td.trip_id')
            ->join('purchase_yards as py', 'py.id = td.purchase_yard_id')
            ->join('product_categories as pc', 'pc.id = t.category_id')
            ->join('purchase_yard_currency_funds as tycf', 'tycf.id = td.purchase_yard_currency_fund_id', 'left')
            ->join('currencies as c', 'c.id = tycf.currency_id', 'left')
            ->join('users as u', 'u.id = delivery_adjustments.created_by', 'left')
            ->join('users as ua', 'ua.id = delivery_adjustments.approved_by', 'left')
            ->where('delivery_adjustments.id', $id)
            ->first();
    }

    /**
     * Đếm tổng số phiếu điều chỉnh xuất kho của người dùng
     * 
     * Hàm này đếm số lượng phiếu điều chỉnh xuất kho mà người dùng
     * đã tạo hoặc có quyền truy cập dựa trên danh sách bãi được phân quyền.
     * Kết quả được sử dụng cho phân trang.
     *
     * @param int $userId ID của người dùng cần lấy dữ liệu
     * @param array $yardIds Danh sách ID bãi mà người dùng có quyền truy cập
     * @return int Tổng số phiếu điều chỉnh
     */
    public function countAdjustmentsByUserId($userId, $yardIds = [])
    {
        $builder = $this->select('COUNT(delivery_adjustments.id) as total')
            ->join('trip_details as td', 'td.id = delivery_adjustments.delivery_id')
            ->join('trips as t', 't.id = td.trip_id');
            
        // Kiểm tra quyền truy cập theo bãi
        if (!empty($yardIds)) {
            $builder->whereIn('td.purchase_yard_id', $yardIds);
        }
        
        // Lấy số lượng bản ghi
        $result = $builder->get()->getRowArray();
        
        return $result ? (int)$result['total'] : 0;
    }
    
} 
