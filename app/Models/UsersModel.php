<?php

namespace App\Models;

use App\Models\BaseModel;

class UsersModel extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'password', 'remember_token', 'status', 'created_at', 'updated_at'];
    
    protected $beforeInsert = ['hashPasswordAndToken'];
    protected $beforeUpdate = ['hashPasswordAndToken'];

    protected function hashPasswordAndToken(array $data)
    {
        if (isset($data['data']['password'])) {
            if (strlen($data['data']['password']) !== 60) {
                $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            }
        }
        if (isset($data['data']['remember_token'])) {
            if (!empty($data['data']['remember_token']) && strlen($data['data']['remember_token']) !== 60) {
                $data['data']['remember_token'] = password_hash($data['data']['remember_token'], PASSWORD_DEFAULT);
            }
        }
        return $data;
    }

    public function authenticate($username, $password, $remember_token = null)
    {
        $user = $this->findOneByUsername($username);
        if (!$user) {
            return false;
        }
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        if ($remember_token) {
            $hashedToken = password_hash($remember_token, PASSWORD_DEFAULT);
            $this->update($user['id'], ['remember_token' => $hashedToken]);
            helper('cookie');
            // Lưu token gốc vào cookie cho 30 ngày
            set_cookie('remember_token', $remember_token, 86400 * 30);
            $user['remember_token'] = $hashedToken;
        }
        return $user;
    }

    /**
     * Authenticate by Remember Token:
     * Lấy token từ cookie và xác thực.
     *
     * @return array|false Thông tin user nếu token hợp lệ, false nếu không.
     */
    public function authenticateByRememberToken()
    {
        helper('cookie');
        $remember_token = get_cookie('remember_token');
        if (!$remember_token) {
            return false;
        }
        $user = $this->findOneByRemember_token($remember_token);
        if ($user && password_verify($remember_token, $user['remember_token'])) {
            return $user;
        }
        return false;
    }

    /**
     * Logout: Xoá remember_token trong DB và cookie.
     *
     * @param int $userId
     * @return bool
     */
    public function logout($userId)
    {
        $result = $this->update($userId, ['remember_token' => null]);
        helper('cookie');
        delete_cookie('remember_token');
        return $result;
    }
    
    /**
     * Kiểm tra quyền truy cập chung.
     * 
     * @param int $userId
     * @param string $controller Tương ứng với trường "action" trong bảng permissions
     * @param string $method     Tương ứng với trường "method" trong bảng permissions
     * @return bool True nếu có quyền, false nếu không.
     */
    public function hasAccess($userId, $controller, $method)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('user_role_assignments');
        $builder->select('permissions.id');
        $builder->join('role_permission', 'role_permission.role_id = user_role_assignments.role_id', 'inner');
        $builder->join('permissions', 'permissions.id = role_permission.permission_id', 'inner');
        $builder->where('user_role_assignments.user_id', $userId);
        $builder->where('permissions.action', $controller);
        $builder->where('permissions.method', $method);
        $query = $builder->get();
        return $query->getNumRows() > 0;
    }

    /**
     * Kiểm tra quyền truy cập chi tiết trên dữ liệu.
     * 
     * @param int $userId
     * @param int|array $validRoles Danh sách role id hợp lệ (hoặc role id duy nhất) mà user cần có.
     * @param int|null $purchaseYardId (Tùy chọn) Nếu dữ liệu liên quan đến bãi, kiểm tra purchase_yard_id.
     * @return bool True nếu user có ít nhất 1 assignment hợp lệ, false nếu không.
     */
    public function isValidRole($userId, $validRoles, $purchaseYardId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('user_role_assignments');
        $builder->where('user_id', $userId);
        if (is_array($validRoles)) {
            $builder->whereIn('role_id', $validRoles);
        } else {
            $builder->where('role_id', $validRoles);
        }
        if ($purchaseYardId !== null) {
            $builder->where('purchase_yard_id', $purchaseYardId);
        }
        $query = $builder->get();
        return $query->getNumRows() > 0;
    }

    /**
     * Trả về mapping quyền của user.
     *
     * Danh sách role cố định:
     * - "Nhân viên bãi"             => PURCHASE_YARD_EMPLOYEE
     * - "Thủ quỹ của bãi"            => PURCHASE_YARD_CASHIER
     * - "Điều phối xe"               => DISPATCHER
     * - "Kiểm soát tài chính kho bãi"=> FINANCE_CONTROLLER
     * - "Quản lý kho"               => WAREHOUSE_MANAGER
     * - "Kế toán công nợ"           => DEBT_ACCOUNTANT
     * - "Thủ quỹ"                   => CASHIER
     * - "Quản lý bán hàng"          => SALES_MANAGER
     * - "Phụ trách chi phí hải quan" => CUSTOMS_COST_CONTROLLER
     * - "Quản lý chi phí hải quan"   => CUSTOMS_COST_MANAGER
     * - "Quản lý hệ thống"           => SYSTEM_MANAGER
     *
     * @param int $userId
     * @return array Mảng mapping, ví dụ: ['PURCHASE_YARD_EMPLOYEE' => [[1]], 'DISPATCHER' => [] , ...]
     */
    public function getUserRolePermissionsMapping($userId)
    {
        $db = \Config\Database::connect();
        // Thực hiện truy vấn một lần để lấy tất cả role của user
        $builder = $db->table('user_role_assignments');
        $builder->select('r.name');
        $builder->join('roles as r', 'r.id = user_role_assignments.role_id', 'inner');
        $builder->where('user_role_assignments.user_id', $userId);
        $query = $builder->get();
        $userRoles = [];
        foreach ($query->getResultArray() as $row) {
            $userRoles[] = $row['name'];
        }
        
        // Danh sách role cố định theo văn bản gốc và mapping sang key tiếng Anh mong muốn:
        $rolesMapping = [
            'Nhân viên bãi'              => 'PURCHASE_YARD_EMPLOYEE',
            'Thủ quỹ của bãi'             => 'PURCHASE_YARD_CASHIER',
            'Điều phối xe'                => 'DISPATCHER',
            'Kiểm soát tài chính kho bãi' => 'FINANCE_CONTROLLER',
            'Quản lý kho'                => 'WAREHOUSE_MANAGER',
            'Kế toán công nợ'            => 'DEBT_ACCOUNTANT',
            'Thủ quỹ'                    => 'CASHIER',
            'Quản lý bán hàng'           => 'SALES_MANAGER',
            'Phụ trách chi phí hải quan'  => 'CUSTOMS_COST_CONTROLLER',
            'Quản lý chi phí hải quan'    => 'CUSTOMS_COST_MANAGER',
            'Quản lý hệ thống'            => 'SYSTEM_MANAGER'
        ];
    
        $result = [];
        foreach ($rolesMapping as $roleName => $key) {
            $result[$key] = in_array($roleName, $userRoles) ? [[1]] : [];
        }
        return $result;
    }
      
}
