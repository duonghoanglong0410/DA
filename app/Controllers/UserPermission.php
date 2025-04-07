<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\UserRoleAssignmentModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class UserPermission extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $userRoleModel;

    // Sử dụng initController của CodeIgniter 4 thay vì __construct()
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel     = new UserModel();
        $this->roleModel     = new RoleModel();
        $this->userRoleModel = new UserRoleAssignmentModel();
    }

    // Mỗi controller phải implement hàm isValidRole, trả về true mặc định
    public function isValidRole($role, $method, $segments)
    {
        return true;
    }

    // GET: Liệt kê danh sách người dùng (view: getUserList.php)
    public function getUserList()
    {
        // Lấy danh sách người dùng có status = 1
        $users = $this->userModel->where('status', 1)->findAll();
        // Lấy danh sách vai trò
        $roles = $this->roleModel->findAll();
        // Lấy tất cả các phân công vai trò
        $assignments = $this->userRoleModel->findAll();

        // Tạo mapping: user_id => mảng tên vai trò (không trùng lặp)
        $userRoles = [];
        foreach ($assignments as $assign) {
            foreach ($roles as $role) {
                if ($role['id'] == $assign['role_id']) {
                    if (!isset($userRoles[$assign['user_id']])) {
                        $userRoles[$assign['user_id']] = [];
                    }
                    if (!in_array($role['name'], $userRoles[$assign['user_id']])) {
                        $userRoles[$assign['user_id']][] = $role['name'];
                    }
                    break;
                }
            }
        }

        // Với mỗi người dùng, nối tên vai trò thành chuỗi
        foreach ($users as &$user) {
            if (isset($userRoles[$user['id']])) {
                $user['role_names'] = implode(', ', $userRoles[$user['id']]);
            } else {
                $user['role_names'] = '';
            }
        }
        unset($user);

        $this->assign('users', $users);
        return $this->render();
    }

    // GET: Hiển thị form thêm người dùng (view: getAdd.php)
    public function getAdd()
    {
        // Gán giá trị mặc định cho form
        $this->assign('username', '');
        $this->assign('fullname', '');
        return $this->render();
    }

    // POST: Xử lý thêm người dùng (với jQuery Ajax, view gọi URL: {site_url}user-permission/add)
    public function postAdd()
    {
        $post = $this->request->getPost();
        $username = trim($post['username']);
        $password = $post['password'];
        $confirmPassword = $post['confirm_password'];
        $fullname = $post['fullname'];
    
        // Kiểm tra xác nhận mật khẩu
        if ($password !== $confirmPassword) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Mật khẩu và xác nhận mật khẩu không khớp.'
            ]);
        }
    
        // Kiểm tra username trùng
        if ($this->userModel->where('username', $username)->first()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Tên đăng nhập đã tồn tại.'
            ]);
        }
    
        // Kiểm tra độ phức tạp của mật khẩu sử dụng verifyPasswordPolicy từ UserModel.
        // Nếu mật khẩu đạt tiêu chuẩn, hàm trả về false; nếu không đạt, trả về thông báo lỗi.
        $policyError = $this->userModel->verifyPasswordPolicy($password);
        if ($policyError) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $policyError
            ]);
        }
    
        // Thêm người dùng mới. Lưu ý: Mật khẩu sẽ được hash tự động bởi hàm hashPasswordAndToken trong UserModel.
        $data = [
            'username'   => $username,
            'password'   => $password,
            'fullname'   => $fullname,
            'status'     => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->userModel->insert($data);
    
        return $this->response->setJSON(['status' => 'success']);
    }
    
    

    // GET: Khoá tài khoản người dùng (lock), cập nhật status về 0
    public function getLock($user_id)
    {
        $data = [
            'status'     => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->userModel->update($user_id, $data);
        return redirect()->to('user-permission');
    }
}
