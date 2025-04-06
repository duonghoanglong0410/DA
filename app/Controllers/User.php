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
    
    /**
     * Hiển thị trang báo lỗi.
     */
    public function getError()
    {
        return $this->render();
    }
}
