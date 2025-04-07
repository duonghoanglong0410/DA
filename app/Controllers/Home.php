<?php

namespace App\Controllers;

class Home extends BaseController
{
    protected function isValidRole($role, $method, $segments)
    {
        return true;
    }

    public function index(): string
    {
        $userId = $this->session->userId;
        // Lấy danh sách menu từ model (không dùng lệnh kết nối DB trực tiếp trong controller)
        $menus = $this->userModel->getUserMainMenu($userId);
        $this->assign('menus', $menus);
        return $this->render();
    }
}
