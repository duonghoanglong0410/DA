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
            return redirect()->to('/');
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
     * Hiển thị trang chỉnh sửa thông tin user.
     * Nếu $id không được truyền vào thì chỉnh sửa thông tin của người dùng hiện tại.
     */
    public function getEdit($id = null)
    {
        // Nếu không truyền id, chỉnh sửa thông tin của user hiện tại
        if ($id === null) {
            $id = $this->session->userId;
        }
        
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->session->setFlashdata('error', 'User không tồn tại.');
            return redirect()->to(site_url('user'));
        }
        
        // Gán các giá trị cần thiết vào view
        $this->assign('id', $user['id']);
        $this->assign('username', $user['username']);
        $this->assign('fullname', $user['fullname']);
        // Bạn có thể gán thêm các trường khác nếu cần
        
        // Lấy flashdata (nếu có) và assign vào view theo chuẩn parser
        $error = $this->session->getFlashdata('error');
        if (empty($error)) {
            $this->assign('error', []);
        } else {
            $this->assign('error', [['mess' => $error]]);
        }
        
        $success = $this->session->getFlashdata('success');
        if (empty($success)) {
            $this->assign('success', []);
        } else {
            $this->assign('success', [['mess' => $success]]);
        }
        
        return $this->render();
    }
    
    /**
     * Xử lý cập nhật thông tin user sau khi chỉnh sửa.
     * Nếu $id không truyền vào thì cập nhật thông tin của người dùng hiện tại.
     */
    public function postEdit($id = null)
    {
        if ($id === null) {
            $id = $this->session->userId;
        }
        
        // Lấy dữ liệu cơ bản từ form
        $data = [
            // 'username' => $this->request->getPost('username'),
            'fullname' => $this->request->getPost('fullname'),
        ];
        
        $password = $this->request->getPost('password');
        $confirm_password = $this->request->getPost('confirm_password');
        
        // Nếu người dùng nhập mật khẩu mới, kiểm tra quy tắc
        if (!empty($password)) {
            // Kiểm tra mật khẩu có ít nhất 8 ký tự, chứa chữ hoa, chữ thường và ít nhất một ký tự đặc biệt
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,}$/', $password)) {
                $this->session->setFlashdata('error', 'Mật khẩu phải có ít nhất 8 ký tự, chứa chữ hoa, chữ thường và ít nhất một ký tự đặc biệt.');
                return redirect()->to(site_url("user/edit/{$id}"));
            }
            // Kiểm tra xác nhận mật khẩu
            if ($password !== $confirm_password) {
                $this->session->setFlashdata('error', 'Mật khẩu xác nhận không khớp.');
                return redirect()->to(site_url("user/edit/{$id}"));
            }
            $data['password'] = $password; // Sẽ được BaseModel hash qua beforeUpdate
        }
        
        if ($this->userModel->update($id, $data)) {
            $this->session->setFlashdata('success', 'Thông tin user đã được cập nhật.');
        } else {
            $this->session->setFlashdata('error', 'Cập nhật thông tin user thất bại.');
        }
        
        return redirect()->to(site_url("user/edit/{$id}"));
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
