<?php

namespace App\Models;

use App\Models\BaseModel;

class UserRoleAssignmentModel extends BaseModel
{
    protected $table = 'user_role_assignments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'role_id', 'purchase_yard_id', 'cash_fund_id', 'warehouse_id', 'created_at', 'updated_at'
    ];

    /**
     * Lấy danh sách phân quyền của tất cả user, bao gồm thông tin user, role và (nếu có) bãi thu mua.
     *
     * @return array Danh sách phân quyền với các trường:
     *                user_role_assignments.*, u.username, r.name as role_name, py.yard_name (nếu có)
     */
    public function getAssignmentsWithUserAndRole()
    {
        $builder = $this->db->table($this->table . ' as ura');
        $builder->select('ura.*, u.username, r.name as role_name, COALESCE(py.yard_name, w.name) as yard_name');
        $builder->join('users as u', 'u.id = ura.user_id', 'left');
        $builder->join('roles as r', 'r.id = ura.role_id', 'left');
        $builder->join('purchase_yards as py', 'py.id = ura.purchase_yard_id', 'left');
        $builder->join('warehouse as w', 'w.id = ura.warehouse_id', 'left');
        $query = $builder->get();
        return $query->getResultArray();
    }
    

    /**
     * Lấy danh sách user_id của người quản lý kho từ bảng user_role_assignments 
     * dựa theo warehouse_id và role_id.
     *
     * @param int $warehouseId ID của kho cần lấy.
     * @param int $roleId ID của role người quản lý kho (ví dụ: role_id của quản lý kho).
     * @return array Danh sách user_id của người quản lý (nếu có nhiều, trả về mảng các user_id).
     */
    public function getManagerIdsByWarehouse($warehouseId, $roleId)
    {
        // Tạo builder cho bảng user_role_assignments
        $builder = $this->db->table($this->table);
        
        // Chọn trường user_id
        $builder->select('user_id');
        
        // Điều kiện: warehouse_id bằng $warehouseId
        $builder->where('warehouse_id', $warehouseId);
        
        // Điều kiện: role_id bằng $roleId
        $builder->where('role_id', $roleId);
        
        // Thực hiện truy vấn
        $query = $builder->get();
        
        // Lấy kết quả dạng mảng
        $results = $query->getResultArray();
        
        $managerIds = [];
        // Lặp qua kết quả và thu thập các user_id
        foreach ($results as $row) {
            $managerIds[] = $row['user_id'];
        }
        return $managerIds;
    }    


    /**
     * Xóa các bản ghi trong bảng user_role_assignments dựa theo warehouse_id và role_id.
     *
     * @param int $warehouseId ID của kho cần xóa phân quyền.
     * @param int $roleId ID của vai trò người quản lý kho.
     * @return bool|int Số bản ghi bị xóa nếu thành công, hoặc false nếu thất bại.
     */
    public function deleteByWarehouseAndRole($warehouseId, $roleId)
    {
        $builder = $this->db->table($this->table);
        // Ghi chú: Xóa các bản ghi có warehouse_id và role_id tương ứng.
        $builder->where('warehouse_id', $warehouseId);
        $builder->where('role_id', $roleId);
        return $builder->delete();
    }

    public function deleteByWarehouseAndRoleWithUserIds($warehouseId, $roleId, $userIds)
    {
        $builder = $this->db->table($this->table);
        // Xóa các bản ghi có warehouse_id, role_id và user_id thuộc danh sách $userIds
        $builder->where('warehouse_id', $warehouseId);
        $builder->where('role_id', $roleId);
        $builder->whereIn('user_id', $userIds);
        return $builder->delete();
    }
    
    /**
     * Lấy danh sách bãi được phân quyền cho user
     *
     * @param int $userId ID của người dùng
     * @return array
     */
    public function getAuthorizedYardsByUserId($userId)
    {
        $this->select('user_role_assignments.purchase_yard_id, purchase_yards.yard_name, purchase_yards.yard_code');
        $this->join('purchase_yards', 'purchase_yards.id = user_role_assignments.purchase_yard_id', 'inner');
        $this->where('user_role_assignments.user_id', $userId);
        $this->where('user_role_assignments.purchase_yard_id IS NOT NULL');
        $this->where('purchase_yards.status', 1); // Chỉ lấy bãi đang hoạt động
        
        return $this->findAll();
    }

    /**
     * Lấy danh sách user có một vai trò cụ thể
     *
     * @param int $roleId ID của vai trò cần kiểm tra
     * @return array Danh sách user_id có vai trò được chỉ định
     */
    public function getUsersWithRole($roleId)
    {
        $this->select('distinct(user_id) as user_id');
        $this->where('role_id', $roleId);
        
        return $this->findAll();
    }
}
