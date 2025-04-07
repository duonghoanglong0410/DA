<?php

namespace App\Controllers;

use App\Models\UsersModel;
use App\Models\RolesModel;
use App\Models\UserRoleAssignmentsModel;
use App\Models\PurchaseYardsModel;

class UserPermission extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $userRoleModel;
    protected $purchaseYardsModel;

    protected function isValidRole($role, $method)
    {
        return true;
        // return $this->userModel->hasRole($this->session->userId, Roles::SYSTEM_MANAGER);
    }        


    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->userModel = new UsersModel();
        $this->roleModel = new RolesModel();
        $this->userRoleModel = new UserRoleAssignmentsModel();
        $this->purchaseYardsModel = new PurchaseYardsModel();
    }

    /**
     * Hiển thị trang chỉnh sửa phân quyền cho một user.
     * Nếu $userId không truyền vào, sử dụng user hiện tại từ session.
     */
    public function getEdit($userId = null)
    {
        if ($userId === null) {
            $userId = $this->session->userId;
        }
        $user = $this->userModel->find($userId);
        if (!$user) {
            $this->session->setFlashdata('error', 'User không tồn tại.');
            return redirect()->to("{site_url}user-permission");
        }
        
        // Lấy tất cả các role
        $roles = $this->roleModel->findAll();
        // Lấy danh sách role đã gán cho user
        $userAssignments = $this->userRoleModel->where('user_id', $userId)->findAll();
        // Mapping: role_id => purchase_yard_id (có thể là null)
        $assignedRoles = [];
        foreach ($userAssignments as $ua) {
            $assignedRoles[$ua['role_id']] = $ua['purchase_yard_id'];
        }
        
        // Lấy danh sách bãi thu mua
        $purchaseYards = $this->purchaseYardsModel->findAll();
        
        // Xử lý dữ liệu để tạo mảng flatRoles dùng trong view (không dùng if phức tạp trong view)
        $flatRoles = [];
        foreach ($roles as $role) {
            $roleData = [];
            $roleData['id']   = $role['id'];
            $roleData['name'] = $role['name'];
            $roleData['target_type'] = $role['target_type']; // "1" nếu phân theo bãi, "0" nếu không
            // Nếu role đã được gán, đánh dấu checkbox
            if (isset($assignedRoles[$role['id']]) && !empty($assignedRoles[$role['id']])) {
                $roleData['checked'] = 'checked="checked"';
                $selected = $assignedRoles[$role['id']];
            } else {
                $roleData['checked'] = '';
                $selected = '';
            }
            // Nếu role có target_type = 1, xây dựng HTML dropdown chọn bãi
            if ((int)$role['target_type'] === 1) {
                $dropdown = '<div class="form-group mt-1 ms-4">';
                $dropdown .= '<label for="purchase_yard_' . $role['id'] . '">Chọn bãi cho ' . $role['name'] . ':</label>';
                $dropdown .= '<select class="form-control" name="purchase_yard_' . $role['id'] . '" id="purchase_yard_' . $role['id'] . '">';
                $dropdown .= '<option value="">-- Chọn bãi --</option>';
                foreach ($purchaseYards as $py) {
                    $dropdown .= '<option value="' . $py['id'] . '"';
                    if ($selected == $py['id']) {
                        $dropdown .= ' selected';
                    }
                    $dropdown .= '>' . $py['yard_name'] . '</option>';
                }
                $dropdown .= '</select></div>';
                $roleData['dropdown'] = $dropdown;
            } else {
                $roleData['dropdown'] = '';
            }
            $flatRoles[] = $roleData;
        }
        
        // Assign các biến đơn cho view
        $this->assign('user_id', $user['id']);
        $this->assign('user_username', $user['username']);
        $this->assign('flatRoles', $flatRoles);
        // Cũng assign danh sách bãi thu mua để dùng nếu cần (đã dùng trong controller để build dropdown)
        $this->assign('purchaseYards', $purchaseYards);
        
        return $this->render();
    }

    /**
     * Cập nhật phân quyền của user dựa trên dữ liệu gửi từ form.
     * Nếu role có target_type = 1, bắt buộc phải chọn bãi.
     */
    public function postEdit($userId = null)
    {
        if ($userId === null) {
            $userId = $this->session->userId;
        }
        $selectedRoles = $this->request->getPost('roles'); // Mảng role_id
        // Xoá tất cả phân quyền của user
        $this->userRoleModel->where('user_id', $userId)->delete();
        
        // Với mỗi role được chọn, insert vào bảng user_role_assignments.
        if (!empty($selectedRoles) && is_array($selectedRoles)) {
            foreach ($selectedRoles as $roleId) {
                $data = [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'purchase_yard_id' => null
                ];
                // Nếu role có target_type = 1, giá trị bãi được gửi từ form với tên "purchase_yard_{roleId}"
                // Lấy dữ liệu từ POST:
                $role = $this->roleModel->find($roleId);
                if ($role && (int)$role['target_type'] === 1) {
                    $py = $this->request->getPost("purchase_yard_{$roleId}");
                    if (empty($py)) {
                        $this->session->setFlashdata('error', 'Vui lòng chọn bãi cho role có phân theo bãi.');
                        return redirect()->to("{site_url}user-permission/edit/{$userId}");
                    }
                    $data['purchase_yard_id'] = $py;
                }
                $this->userRoleModel->insert($data);
            }
        }
        
        $this->session->setFlashdata('success', 'Phân quyền đã được cập nhật.');
        return redirect()->to("{site_url}user-permission/edit/{$userId}");
    }
}
