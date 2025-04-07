<?php

namespace App\Controllers;

use App\Models\PurchaseYardsModel;

class Yard extends BaseController
{
    protected $purchaseYardsModel;

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
        $this->purchaseYardsModel = new PurchaseYardsModel();
    }
    
    /**
     * Liệt kê tất cả các kho bãi.
     */
    public function getIndex()
    {
        $yards = $this->purchaseYardsModel->findAll();
        foreach ($yards as &$yard) {
            $yard['status_text'] = ($yard['status'] == 1) ? 'Hoạt động' : 'Đã ngừng';
        }
        $this->assign('yards', $yards);
        
        return $this->render(); // Tự động load view: app/Views/yard/getIndex.php
    }
    
    /**
     * Hiển thị trang thêm mới kho bãi.
     * Tự động tạo mã bãi mới theo định dạng Yxxxx.
     */
    public function getAdd()
    {
        // Lấy mã bãi mới từ model
        $newCode = $this->purchaseYardsModel->getNextYardCode();
        $this->assign('yard_code', $newCode);
        return $this->render();
    }
    
    
    /**
     * Xử lý thêm mới kho bãi.
     */
    public function postAdd()
    {
        $data = [
            'yard_code'      => $this->request->getPost('yard_code'),
            'yard_name'      => $this->request->getPost('yard_name'),
            'client_api_key' => $this->request->getPost('client_api_key'),
            'status'         => $this->request->getPost('status') ? 1 : 0,
        ];
        
        if ($this->purchaseYardsModel->insert($data)) {
            $this->session->setFlashdata('success', 'Kho bãi đã được thêm mới.');
        } else {
            $this->session->setFlashdata('error', 'Thêm mới kho bãi thất bại.');
        }
        return redirect()->to("yard");
    }
    
    /**
     * Hiển thị form chỉnh sửa kho bãi.
     */
    public function getEdit($id)
    {
        $yard = $this->purchaseYardsModel->find($id);
        if (!$yard) {
            $this->session->setFlashdata('error', 'Kho bãi không tồn tại.');
            return redirect()->to("yard");
        }
        // Assign các biến đơn để view hiển thị (không dùng {yard.yard_name})
        $this->assign('yard_id', $yard['id']);
        $this->assign('yard_code', $yard['yard_code']);
        $this->assign('yard_name', $yard['yard_name']);
        $this->assign('client_api_key', $yard['client_api_key']);
        $this->assign('status', $yard['status']);
        
        return $this->render(); // Tự động load view: app/Views/yard/getEdit.php
    }
    
    /**
     * Xử lý cập nhật kho bãi.
     */
    public function postEdit($id)
    {
        $data = [
            'yard_code'      => $this->request->getPost('yard_code'),
            'yard_name'      => $this->request->getPost('yard_name'),
            'client_api_key' => $this->request->getPost('client_api_key'),
            'status'         => $this->request->getPost('status') ? 1 : 0,
        ];
        
        if ($this->purchaseYardsModel->update($id, $data)) {
            $this->session->setFlashdata('success', 'Kho bãi đã được cập nhật.');
        } else {
            $this->session->setFlashdata('error', 'Cập nhật kho bãi thất bại.');
        }
        return redirect()->to("{site_url}yard/edit/{$id}");
    }
    
    /**
     * Xóa kho bãi.
     */
    public function getDelete($id)
    {
        if ($this->purchaseYardsModel->delete($id)) {
            $this->session->setFlashdata('success', 'Kho bãi đã được xóa.');
        } else {
            $this->session->setFlashdata('error', 'Xóa kho bãi thất bại.');
        }
        return redirect()->to("yard");
    }
}
