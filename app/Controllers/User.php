<?php

namespace App\Controllers;

class User extends BaseController
{
    protected function isValidRole($role, $method)
    {
        return true;
    }

    /**
     * Hiển thị trang login.
     * Nếu view có tên trùng với method (getLogin), chỉ cần gọi render().
     */
    public function getLogin()
    {
        $error = $this->session->getFlashdata('error');

        if (empty($error))
        {
            $this->assign('error', []);
        }
        else
        {
            $this->assign('error', [['mess' => $error]]);
        }

        return $this->render();
    }
    
    /**
     * Xử lý authentication từ form login.
     */
    public function postAuthencation()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        
        // Nếu checkbox remember được chọn, tạo token ngẫu nhiên
        if ($this->request->getPost('remember')) {
            $remember_token =  uniqid() . bin2hex(random_bytes(16)); // tạo token 32 ký tự hex
        } else {
            $remember_token = null;
        }

        $user = $this->userModel->authenticate($username, $password, $remember_token);
        if ($user) {
            $this->session->set('userId', $user['id']);
            return redirect()->to('/home');
        } else {
            $this->session->setFlashdata('error', 'Thông tin đăng nhập không chính xác.');
            return redirect()->to('/user/login');
        }
    }

    public function getLogout()
    {
        // Lấy userId từ session
        $userId = $this->session->userId;
        if ($userId) {
            // Gọi hàm logout của model để xoá remember_token khỏi DB và xoá cookie
            $this->userModel->logout($userId);
            // Xoá userId khỏi session
            $this->session->remove('userId');
        }
        // Chuyển hướng về trang login
        return redirect()->to(site_url('user/login'));
    }    
    
    /**
     * Hiển thị trang báo lỗi.
     */
    public function getError($mid = '')
    {
        switch ($mid) {
            case 'i001':
                $this->assign('mtitle', 'Không thể in phiếu xuất');
                $this->assign('message', '<p>Hệ thống chỉ hỗ trợ in phiếu xuất kho cho các đơn hàng được tạo từ báo giá.</p><p>Đơn hàng này được tạo thủ công từ trang quản lý giao vận nên không đủ thông tin để lập phiếu xuất.</p>');
                break;
            default:
                $this->assign('mtitle', 'OPPSSS!!!! Rất tiếc...');
                $this->assign('message', 'Bạn không đủ quyền để thực hiện chức năng này. Nếu bạn cho rằng đây là một sự nhầm lẫn, xin vui lòng liên hệ với nhân viên quản lý trực tiếp để được hướng dẫn.');
        }
        return $this->render();
    }
}
