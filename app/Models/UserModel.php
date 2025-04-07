<?php

namespace App\Models;

use App\Constants\Roles;
use App\Models\BaseModel;

class UserModel extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'password', 'remember_token', 'status', 'created_at', 'updated_at', 'fullname'];

    protected $beforeInsert = ['hashPasswordAndToken'];
    protected $beforeUpdate = ['hashPasswordAndToken'];

    public function verifyPasswordPolicy($password)
    {
        // Kiểm tra độ phức tạp của mật khẩu: ít nhất 8 ký tự, chứa chữ hoa, chữ thường, số và ít nhất 1 ký tự đặc biệt
        $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
        if (!preg_match($regex, $password)) {
            return 'Mật khẩu phải có ít nhất 8 ký tự, chứa chữ hoa, chữ thường, số và ít nhất 1 ký tự đặc biệt.';
        }

        return false;
    }

    private function hashAString($str)
    {
        // Kiểm tra nếu chuỗi là base64 hợp lệ
        if ($this->isValidBase64($str)) {
            $decoded = base64_decode($str, true);
            // Nếu chuỗi decoded bắt đầu bằng "$2y$10$", nghĩa là đã được hash rồi
            if (strpos($decoded, '$2y$10$') === 0) {
                return $str;
            }
        }
        // Nếu không, tiến hành hash và encode
        return base64_encode(password_hash($str, PASSWORD_BCRYPT));
    }
    
    /**
     * Kiểm tra xem chuỗi có phải là base64 hợp lệ hay không.
     *
     * @param string $s
     * @return bool
     */
    private function isValidBase64($s)
    {
        // So sánh chuỗi encode lại của decoded với chuỗi gốc
        return (base64_encode(base64_decode($s, true)) === $s);
    }
    

    protected function hashPasswordAndToken(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = $this->hashAString($data['data']['password']);
        }
        if (!empty($data['data']['remember_token'])) {
            $data['data']['remember_token'] = $this->hashAString($data['data']['remember_token']);
        }
        return $data;
    }

    public function authenticate($username, $password, $remember_token = null)
    {
        $user = $this->findOneByUsername($username);
        if (!$user || empty($user['status'])) {
            return false;
        }
        if (!password_verify($password, base64_decode($user['password']))) {

            return false;
        }
        if ($remember_token) {
            // $hashedToken = $this->hashAString($remember_token);
            $this->update($user['id'], ['remember_token' => $remember_token]);
            helper('cookie');
            // Lưu token gốc vào cookie cho 30 ngày
            set_cookie('remember_token', $remember_token, 86400 * 30);
            // $user['remember_token'] = $hashedToken;
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
        return true;
        // Mảng các controller có truy cập công khai với các method cho phép.
        // Lưu ý: Controller được lưu dưới dạng chữ thường.
        $publicAccess = [
            'user' => ['getLogin', 'postAuthencation', 'getError', 'getEdit', 'postEdit'],
            'home' => ['index'],
        ];
        
        // Kiểm tra nếu controller và method nằm trong danh sách public access
        $controllerLower = strtolower($controller);
        if (isset($publicAccess[$controllerLower]) && in_array($method, $publicAccess[$controllerLower])) {
            return true;
        }

        $db = \Config\Database::connect();
        $builder = $db->table('user_role_assignments');
        $builder->select('permissions.id');
        $builder->join('role_permission', 'role_permission.role_id = user_role_assignments.role_id', 'inner');
        $builder->join('permissions', 'permissions.id = role_permission.permission_id', 'inner');
        $builder->where('user_role_assignments.user_id', $userId);
        $builder->where('permissions.action', $controllerLower);
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
     * Lấy danh sách menu (permission với is_main_menu = 1) mà user được cấp,
     * nhóm theo trường "group" theo yêu cầu của hệ thống.
     *
     * @param int $userId
     * @return array Mảng mapping, ví dụ: 
     *   [
     *      'PURCHASE_YARD_EMPLOYEE' => [[1]], 
     *      'DISPATCHER' => [] , 
     *      ...
     *   ]
     */
    public function getUserRolePermissionsMapping($userId)
    {
        $builder = $this->db->table('user_role_assignments');
        $builder->select('r.id');
        $builder->join('roles as r', 'r.id = user_role_assignments.role_id', 'inner');
        $builder->where('user_role_assignments.user_id', $userId);
        $query = $builder->get();
        $userRoles = array_column($query->getResultArray(), 'id');        

        $result = [];
        foreach (Roles::ROLES_MAPPING as $roleId => $menuKey) {
            $result[$menuKey] = in_array($roleId, $userRoles) ? [[1]] : [];
        }
        return $result;
    }


    /**
     * Lấy danh sách menu (permission với is_main_menu = 1) mà user được cấp,
     * nhóm theo trường "group".
     *
     * @param int $userId
     * @return array Mảng menu được nhóm theo group
     */
    public function getUserMainMenu($userId)
    {
        // Sử dụng query builder từ model (không cần kết nối DB trong controller)
        $builder = $this->db->table('user_role_assignments');
        $builder->select('permissions.*');
        $builder->join('role_permission', 'role_permission.role_id = user_role_assignments.role_id', 'inner');
        $builder->join('permissions', 'permissions.id = role_permission.permission_id', 'inner');
        $builder->where('user_role_assignments.user_id', $userId);
        $builder->where('permissions.is_main_menu', 1);
        $query = $builder->get();
        $permissions = $query->getResultArray();

        $menus = [];
        foreach ($permissions as $perm) {
            $group = !empty($perm['group']) ? $perm['group'] : 'UNGROUPED';
            if (!isset($menus[$group])) {
                $menus[$group] = [];
            }
            $menus[$group][] = [
                'title'      => $perm['name'],
                'desc'       => $perm['description'],
                'icon_class' => $perm['icon_class'],
                'action'     => $perm['action'],
                'method'     => $perm['method']
            ];
        }
        // Sắp xếp các group theo thứ tự chữ nếu cần
        ksort($menus);
        return $menus;
    }    

    public function hasRole($userId, $roleId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('user_role_assignments');
        $builder->where('user_id', $userId);
        $builder->where('role_id', $roleId);
        return $builder->countAllResults() > 0;
    }
    
}
