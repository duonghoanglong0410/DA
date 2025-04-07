<?php

namespace App\Controllers;

use App\Models\CashFundsModel;

class CashFund extends BaseController
{
    protected $cashFundsModel;

    /**
     * Implement abstract method isValidRole từ BaseController.
     * Mặc định trả về true.
     *
     * @param string $role
     * @param string $method
     * @param array  $segments
     * @return bool
     */
    protected function isValidRole($role, $method, $segments)
    {
        return true;
    }    

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, 
                                   \CodeIgniter\HTTP\ResponseInterface $response, 
                                   \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->cashFundsModel = new CashFundsModel();
    }
    
    /**
     * Hiển thị danh sách quỹ tiền.
     */
    public function getIndex()
    {
        $funds = $this->cashFundsModel->findAll();
        // Để hiển thị trạng thái và các thông tin cần thiết, ta sử dụng các biến đơn
        // Ví dụ: fund_code, fund_name, id
        $this->assign('funds', $funds);
        return $this->render();
    }
    
    /**
     * Hiển thị form thêm mới quỹ tiền.
     */
    public function getAdd()
    {
        // Có thể tự động tạo mã quỹ nếu cần, hoặc để người dùng nhập
        // Ở đây ta cho phép người dùng nhập
        return $this->render();
    }
    
    /**
     * Xử lý thêm mới quỹ tiền.
     */
    public function postAdd()
    {
        $data = [
            'fund_code' => $this->request->getPost('fund_code'),
            'fund_name' => $this->request->getPost('fund_name'),
        ];
        
        if ($this->cashFundsModel->insert($data)) {
            $this->session->setFlashdata('success', 'Quỹ tiền đã được thêm mới.');
        } else {
            $this->session->setFlashdata('error', 'Thêm mới quỹ tiền thất bại.');
        }
        return redirect()->to("cash-fund");
    }
    
    /**
     * Hiển thị form chỉnh sửa quỹ tiền.
     */
    public function getEdit($id)
    {
        $fund = $this->cashFundsModel->find($id);
        if (!$fund) {
            $this->session->setFlashdata('error', 'Quỹ tiền không tồn tại.');
            return redirect()->to("cash-fund");
        }
        // Assign các biến đơn cho view (không dùng biến phức hợp)
        $this->assign('fund_id', $fund['id']);
        $this->assign('fund_code', $fund['fund_code']);
        $this->assign('fund_name', $fund['fund_name']);
        return $this->render();
    }
    
    /**
     * Xử lý cập nhật thông tin quỹ tiền.
     */
    public function postEdit($id)
    {
        $data = [
            'fund_code' => $this->request->getPost('fund_code'),
            'fund_name' => $this->request->getPost('fund_name'),
        ];
        
        if ($this->cashFundsModel->update($id, $data)) {
            $this->session->setFlashdata('success', 'Quỹ tiền đã được cập nhật.');
        } else {
            $this->session->setFlashdata('error', 'Cập nhật quỹ tiền thất bại.');
        }
        return redirect()->to("cash-fund/edit/{$id}");
    }
    
    /**
     * Xóa quỹ tiền.
     */
    public function getDelete($id)
    {
        if ($this->cashFundsModel->delete($id)) {
            $this->session->setFlashdata('success', 'Quỹ tiền đã được xóa.');
        } else {
            $this->session->setFlashdata('error', 'Xóa quỹ tiền thất bại.');
        }
        return redirect()->to("cash-fund");
    }
}
