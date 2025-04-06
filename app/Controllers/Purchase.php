<?php

namespace App\Controllers;

class Purchase extends BaseController
{
    /**
     * Implement hàm abstract isValidRole trong BaseController.
     * Mặc định trả về true.
     *
     * @param string $role
     * @param string $method
     * @return bool
     */
    protected function isValidRole($role, $method)
    {
        return true;
    }
    
    /**
     * Lập phiếu cân đầu ra.
     */
    public function getGoodsDelivery()
    {
        return $this->render();
    }
    
    /**
     * Lập phiếu cân đầu vào.
     */
    public function getGoodsReceipt()
    {
        return $this->render();
    }
    
    /**
     * Xem danh sách phiếu cân đầu ra.
     */
    public function getDeliveryList()
    {
        return $this->render();
    }
    
    /**
     * Xem danh sách phiếu cân đầu vào.
     */
    public function getReceiptList()
    {
        return $this->render();
    }
    
    /**
     * Danh sách đề nghị điều chỉnh phiếu cân.
     */
    public function getAdjustmentRequests()
    {
        return $this->render();
    }
}
