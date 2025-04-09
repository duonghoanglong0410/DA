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

    /**
     * Hiển thị trang chỉnh sửa phân quyền cho một user.
     * Nếu không truyền userId thì sử dụng userId hiện tại từ session.
     */
    public function getEdit($userId = null)
    {
        if ($userId === null) {
            $userId = $this->session->userId;
        }
        $user = $this->userModel->find($userId);
        if (!$user) {
            $this->session->setFlashdata('error', 'User không tồn tại.');
            return redirect()->to("user-permission");
        }
        
        // Lấy tất cả các role
        $roles = $this->roleModel->findAll();
        // Lấy danh sách phân quyền đã gán cho user
        $userAssignments = $this->userRoleModel->where('user_id', $userId)->findAll();
        // Mapping: role_id => mảng target (target có thể là purchase_yard_id hoặc cash_fund_id)
        $assignedRoles = [];
        foreach ($userAssignments as $ua) {
            $rid = $ua['role_id'];
            if (!isset($assignedRoles[$rid])) {
                $assignedRoles[$rid] = [];
            }
            if (!empty($ua['purchase_yard_id'])) {
                $assignedRoles[$rid][] = $ua['purchase_yard_id'];
            }
        }
        
        // Lấy danh sách purchase yards và cash funds
        $purchaseYards = $this->purchaseYardModel->findAll();
        $cashFunds     = $this->cashFundsModel->findAll();
        
        // Tạo các mảng nhóm role
        $group1 = []; // target_type = 1: theo bãi
        $group2 = []; // target_type = 2: quỹ tiền
        $group3 = []; // target_type = 0: chức năng chung
        
        foreach ($roles as $role) {
            $roleData = [];
            $roleData['id'] = $role['id'];
            $roleData['name'] = $role['name'];
            $roleData['target_type'] = $role['target_type']; // giá trị 1, 2, hoặc 0
            $roleData['checked'] = (isset($assignedRoles[$role['id']]) && count($assignedRoles[$role['id']]) > 0) ? 'checked="checked"' : '';
            
            // Nếu role có target_type = 1 hoặc 2, tạo mảng toggle (sẽ là mảng con)
            $roleData['toggle'] = [];
            if ((int)$role['target_type'] === 1) {
                // Sử dụng danh sách purchase yards cho role target_type = 1
                foreach ($purchaseYards as $py) {
                    $toggle = [];
                    if (isset($assignedRoles[$role['id']]) && in_array($py['id'], $assignedRoles[$role['id']])) {
                        $toggle['toggle_active'] = 'active';
                        $toggle['toggle_checked'] = 'checked';
                    } else {
                        $toggle['toggle_active'] = '';
                        $toggle['toggle_checked'] = '';
                    }
                    $toggle['toggle_role_id'] = $role['id'];
                    $toggle['toggle_y_id'] = $py['id'];
                    $toggle['toggle_yard_name'] = $py['yard_name'];
                    $roleData['toggle'][] = $toggle;
                }
            } elseif ((int)$role['target_type'] === 2) {
                // Sử dụng danh sách cash funds cho role target_type = 2
                foreach ($cashFunds as $cf) {
                    $toggle = [];
                    if (isset($assignedRoles[$role['id']]) && in_array($cf['id'], $assignedRoles[$role['id']])) {
                        $toggle['toggle_active'] = 'active';
                        $toggle['toggle_checked'] = 'checked';
                    } else {
                        $toggle['toggle_active'] = '';
                        $toggle['toggle_checked'] = '';
                    }
                    $toggle['toggle_role_id'] = $role['id'];
                    $toggle['toggle_y_id'] = $cf['id'];
                    $toggle['toggle_yard_name'] = $cf['fund_name'];
                    $roleData['toggle'][] = $toggle;
                }
            }
            
            // Phân nhóm theo target_type
            if ((int)$role['target_type'] === 1) {
                $group1[] = $roleData;
            } elseif ((int)$role['target_type'] === 2) {
                $group2[] = $roleData;
            } else {
                $group3[] = $roleData;
            }
        }
        
        // Assign các biến đơn cho view
        $this->assign('user_id', $user['id']);
        $this->assign('user_username', $user['username']);
        $this->assign('group1', $group1);
        $this->assign('group2', $group2);
        $this->assign('group3', $group3);

        return $this->render();
    }

    /**
     * Cập nhật phân quyền của user dựa trên dữ liệu gửi từ form.
     * Với role có target_type = 1 hoặc 2, bắt buộc phải chọn ít nhất một target.
     */
    public function postEdit($userId = null)
    {
        if ($userId === null) {
            $userId = $this->session->userId;
        }
        // Lấy danh sách role được chọn từ form (checkbox "roles[]")
        $selectedRoles = $this->request->getPost('roles'); // mảng role_id
        
        // Xoá tất cả phân quyền hiện có của user
        $this->userRoleModel->where('user_id', $userId)->delete();

        // Nếu có role được chọn, xử lý từng role
        if (!empty($selectedRoles) && is_array($selectedRoles)) {
            foreach ($selectedRoles as $roleId) {
                $data = [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'purchase_yard_id' => null, // dùng chung cột cho target
                ];
                // Lấy thông tin role để xác định target_type
                $role = $this->roleModel->find($roleId);
                if ($role && ((int)$role['target_type'] === 1 || (int)$role['target_type'] === 2)) {
                    // Lấy các target được chọn từ form với field name "target_{roleId}[]"
                    $targets = $this->request->getPost("target_{$roleId}");
                    if (empty($targets) || !is_array($targets) || count($targets) < 1) {
                        $this->session->setFlashdata('error', 'Vui lòng chọn ít nhất một mục cho role: ' . $role['name']);
                        return redirect()->to("user-permission/edit/{$userId}");
                    }
                    // Với mỗi target, insert một record
                    foreach ($targets as $target) {
                        $data['purchase_yard_id'] = $target;
                        $this->userRoleModel->insert($data);
                    }
                } else {
                    // Với role không có target, chỉ insert một record
                    $this->userRoleModel->insert($data);
                }
            }
        }
        $this->session->setFlashdata('success', 'Phân quyền đã được cập nhật.');

        return redirect()->to("user-permission/edit/{$userId}");
    }
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
        $currentUserId = $this->session->userId;
        
        // Kiểm tra nếu người dùng cố gắng khóa tài khoản của chính mình
        if ($currentUserId == $user_id) {
            return redirect()->to('user-permission/user-list')->with('error', 'Không được khóa tài khoản của chính bạn.');
        }
        
        // Cập nhật status của người dùng cần khóa về 0
        $data = [
            'status'     => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->userModel->update($user_id, $data);
        
        return redirect()->to('user-permission/user-list');
    }
    
    
    
}
