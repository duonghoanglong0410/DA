<?php namespace App\Models;

use App\Models\BaseModel;

class PurchaseYardGoodsReceiptModel extends BaseModel
{
    protected $table      = 'purchase_yard_goods_receipts';
    protected $primaryKey = 'id';
    
    // Các cột được phép thao tác theo cấu trúc bảng
    protected $allowedFields = [
        'purchase_yard_id',
        'vehicle_number',
        'category_id',
        'weight',
        'unit_price',
        'created_by',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Thêm mới phiếu nhập hàng
     *
     * @param array $data Dữ liệu phiếu nhập hàng
     * @return int ID của phiếu mới thêm
     */
    public function insert($data = null, bool $returnID = true)
    {
        // Thêm thời gian tạo và cập nhật
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Đảm bảo có thông tin người tạo nếu chưa được đặt
        if (!isset($data['created_by'])) {
            $session = \Config\Services::session();
            $data['created_by'] = $session->userId ?? null;
        }
        
        return parent::insert($data, $returnID);
    }
    
    /**
     * Lấy danh sách phiếu nhập với thông tin người tạo
     */
    public function getReceiptsWithCreator($date, $yardIds = null)
    {
        $builder = $this->db->table($this->table.' pygr');
        $builder->select('pygr.*, u.fullname as creator_name, pc.name as category_name');
        $builder->join('users u', 'u.id = pygr.created_by', 'left');
        $builder->join('product_categories pc', 'pc.id = pygr.category_id', 'left');
        
        if ($date) {
            $builder->where('DATE(pygr.created_at)', $date);
        }
        
        if ($yardIds) {
            $builder->whereIn('pygr.purchase_yard_id', $yardIds);
        }
        
        $builder->orderBy('pygr.id', 'desc');
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Lấy danh sách phiếu nhập trong khoảng thời gian
     */
    public function getReceiptsWithinDateRange($startDate, $endDate, $yardIds = null)
    {
        $builder = $this->db->table($this->table.' pygr');
        $builder->select('pygr.*, u.fullname as creator_name, pc.name as category_name');
        $builder->join('users u', 'u.id = pygr.created_by', 'left');
        $builder->join('product_categories pc', 'pc.id = pygr.category_id', 'left');
        
        // Filter by date range
        $builder->where('DATE(pygr.created_at) >=', $startDate);
        $builder->where('DATE(pygr.created_at) <=', $endDate);
        
        // Filter by authorized yards
        if ($yardIds) {
            $builder->whereIn('pygr.purchase_yard_id', $yardIds);
        }
        
        // Order by most recent first
        $builder->orderBy('pygr.created_at', 'desc');
        $builder->orderBy('pygr.id', 'desc');
        
        return $builder->get()->getResultArray();
    }
}
