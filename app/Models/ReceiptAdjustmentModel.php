<?php

namespace App\Models;

use App\Models\BaseModel;

class ReceiptAdjustmentModel extends BaseModel
{
    protected $table            = 'receipt_adjustments';
    protected $primaryKey       = 'id';
    
    protected $allowedFields    = [
        'receipt_id',
        'old_weight',
        'old_unit_price',
        'new_weight',
        'new_unit_price',
        'status',
        'created_by',
        'reviewed_by',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Insert a new receipt adjustment request
     * 
     * @param array $data The adjustment data to insert
     * @return int|false The inserted ID on success, false on failure
     */
    public function insertAdjustment($data)
    {
        // Set default values
        $data['status'] = $data['status'] ?? 0; // Pending by default
        $data['reviewed_by'] = $data['reviewed_by'] ?? 0; // Not reviewed yet
        
        // Set timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->insert($data);
    }
    
    /**
     * Get receipt adjustment requests with additional information
     * 
     * @param array $where Optional WHERE conditions
     * @return array The list of adjustment requests with related info
     */
    public function getAdjustmentsWithReceipts($where = [])
    {
        $builder = $this->db->table($this->table.' as ra');
        $builder->select('
            ra.*,
            pygr.purchase_yard_id,
            pygr.vehicle_number,
            pygr.category_id,
            pygr.weight as receipt_weight,
            pygr.unit_price as receipt_unit_price,
            py.yard_name,
            py.yard_code,
            pc.name as category_name,
            creator.fullname as creator_name,
            reviewer.fullname as reviewer_name
        ');
        
        // Join with receipts
        $builder->join('purchase_yard_goods_receipts as pygr', 'pygr.id = ra.receipt_id', 'left');
        
        // Join with yards
        $builder->join('purchase_yards as py', 'py.id = pygr.purchase_yard_id', 'left');
        
        // Join with product categories
        $builder->join('product_categories as pc', 'pc.id = pygr.category_id', 'left');
        
        // Join with users for creator
        $builder->join('users as creator', 'creator.id = ra.created_by', 'left');
        
        // Join with users for reviewer (if available)
        $builder->join('users as reviewer', 'reviewer.id = ra.reviewed_by', 'left');
        
        // Apply WHERE conditions
        if (!empty($where)) {
            $builder->where($where);
        }
        
        // Order by latest first
        $builder->orderBy('ra.created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Approve or reject an adjustment request
     * 
     * @param int $id The adjustment ID
     * @param int $reviewerId The reviewer's user ID
     * @param int $status The new status (1 for approved, 2 for rejected)
     * @return bool True on success, false on failure
     */
    public function updateStatus($id, $reviewerId, $status)
    {
        return $this->update($id, [
            'status' => $status,
            'reviewed_by' => $reviewerId,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Lấy danh sách phiếu điều chỉnh chưa được duyệt theo các bãi được phân quyền
     *
     * @param array $yardIds Danh sách ID của bãi được phân quyền
     * @return array Danh sách phiếu điều chỉnh chưa được duyệt
     */
    public function getPendingAdjustmentsByYardIds($yardIds)
    {
        if (empty($yardIds)) {
            return [];
        }
        
        $this->select('
            receipt_adjustments.*,
            purchase_yard_goods_receipts.purchase_yard_id,
            purchase_yard_goods_receipts.vehicle_number,
            purchase_yard_goods_receipts.category_id,
            purchase_yards.yard_name,
            purchase_yards.yard_code,
            product_categories.name as category_name,
            users.fullname as creator_name
        ');
        
        $this->join('purchase_yard_goods_receipts', 'purchase_yard_goods_receipts.id = receipt_adjustments.receipt_id');
        $this->join('purchase_yards', 'purchase_yards.id = purchase_yard_goods_receipts.purchase_yard_id');
        $this->join('product_categories', 'product_categories.id = purchase_yard_goods_receipts.category_id');
        $this->join('users', 'users.id = receipt_adjustments.created_by', 'left');
        
        $this->where('receipt_adjustments.reviewed_by', 0); // Chỉ lấy phiếu chưa được duyệt
        $this->whereIn('purchase_yard_goods_receipts.purchase_yard_id', $yardIds);
        $this->orderBy('receipt_adjustments.created_at', 'DESC');
        
        return $this->findAll();
    }
} 