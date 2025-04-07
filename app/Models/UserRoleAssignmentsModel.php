<?php

namespace App\Models;

use App\Models\BaseModel;

class UserRoleAssignmentsModel extends BaseModel
{
    protected $table = 'user_role_assignments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'role_id', 'purchase_yard_id', 'created_at', 'updated_at'];

    /**
     * Lấy danh sách phân quyền của tất cả user, bao gồm thông tin user, role và (nếu có) bãi thu mua.
     *
     * @return array Danh sách phân quyền với các trường:
     *                user_role_assignments.*, u.username, r.name as role_name, py.yard_name (nếu có)
     */
    public function getAssignmentsWithUserAndRole()
    {
        $builder = $this->db->table($this->table . ' as ura');
        $builder->select('ura.*, u.username, r.name as role_name, py.yard_name');
        $builder->join('users as u', 'u.id = ura.user_id', 'left');
        $builder->join('roles as r', 'r.id = ura.role_id', 'left');
        $builder->join('purchase_yards as py', 'py.id = ura.purchase_yard_id', 'left');
        $query = $builder->get();
        return $query->getResultArray();
    }
}
