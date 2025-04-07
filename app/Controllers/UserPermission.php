<?php

namespace App\Controllers;

use App\Models\UsersModel;
use App\Models\RolesModel;
use App\Models\UserRoleAssignmentsModel;
use App\Models\PurchaseYardsModel;
use App\Models\CashFundsModel; // Giả sử bạn đã tạo model này
use App\Constants\Roles; // Nếu cần dùng các hằng số cho role

class UserPermission extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $userRoleModel;
    protected $purchaseYardsModel;
    protected $cashFundsModel;

    protected function isValidRole($role, $method, $segments)
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
        $this->cashFundsModel = new CashFundsModel();
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
            return redirect()->to("{site_url}user-permission");
        }
        
        // Lấy tất cả role
        $roles = $this->roleModel->findAll();
        // Lấy danh sách phân quyền đã gán cho user (user_role_assignments)
        $userAssignments = $this->userRoleModel->where('user_id', $userId)->findAll();
        // Tạo mapping: role_id => array các target (purchase_yard_id hoặc cash_fund_id)
        $assignedRoles = [];
        foreach ($userAssignments as $ua) {
            $rid = $ua['role_id'];
            if (!isset($assignedRoles[$rid])) {
                $assignedRoles[$rid] = [];
            }
            // Dùng cột purchase_yard_id để lưu target cho cả target_type 1 và 2
            if (!empty($ua['purchase_yard_id'])) {
                $assignedRoles[$rid][] = $ua['purchase_yard_id'];
            }
        }
        
        // Lấy danh sách bãi thu mua và quỹ tiền tệ
        $purchaseYards = $this->purchaseYardsModel->findAll();
        $cashFunds     = $this->cashFundsModel->findAll();
        
        // Tạo mảng flatRoles: mỗi phần tử chứa các trường đơn để view có thể sử dụng
        $group1 = []; // target_type = 1 (theo bãi)
        $group2 = []; // target_type = 2 (quỹ tiền)
        $group3 = []; // target_type = 0 (chức năng chung)
        
        foreach ($roles as $role) {
            $roleData = [];
            $roleData['id'] = $role['id'];
            $roleData['name'] = $role['name'];
            $roleData['target_type'] = $role['target_type']; // 1,2 hoặc 0
            // Checkbox được đánh dấu nếu có bất kỳ target nào được gán
            $roleData['checked'] = (isset($assignedRoles[$role['id']]) && count($assignedRoles[$role['id']]) > 0) ? 'checked="checked"' : '';
            
            // Xây dựng dropdown tùy thuộc vào target_type
            if ((int)$role['target_type'] === 1) {
                // Dropdown cho purchase yards - cho phép chọn nhiều (multi-select)
                $options = '';
                foreach ($purchaseYards as $py) {
                    $selected = (isset($assignedRoles[$role['id']]) && in_array($py['id'], $assignedRoles[$role['id']])) ? ' selected' : '';
                    $options .= '<option value="' . $py['id'] . '"' . $selected . '>' . $py['yard_name'] . '</option>';
                }
                $dropdown = '<select class="form-control" name="target_' . $role['id'] . '[]" id="target_' . $role['id'] . '" multiple>';
                $dropdown .= $options;
                $dropdown .= '</select>';
                $roleData['dropdown'] = $dropdown;
            } elseif ((int)$role['target_type'] === 2) {
                // Dropdown cho cash funds
                $options = '';
                foreach ($cashFunds as $cf) {
                    $selected = (isset($assignedRoles[$role['id']]) && in_array($cf['id'], $assignedRoles[$role['id']])) ? ' selected' : '';
                    $options .= '<option value="' . $cf['id'] . '"' . $selected . '>' . $cf['fund_name'] . '</option>';
                }
                $dropdown = '<select class="form-control" name="target_' . $role['id'] . '[]" id="target_' . $role['id'] . '" multiple>';
                $dropdown .= $options;
                $dropdown .= '</select>';
                $roleData['dropdown'] = $dropdown;
            } else {
                $roleData['dropdown'] = '';
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
        // Assign các group role đã xử lý
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
                        return redirect()->to("{site_url}user-permission/edit/{$userId}");
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
        return redirect()->to("{site_url}user-permission/edit/{$userId}");
    }
}
