<?php namespace App\Controllers;

use App\Models\WarehousesModel;

class Warehouse extends BaseController
{
    // Khai báo property cho model
    protected $warehousesModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, 
                                   \CodeIgniter\HTTP\ResponseInterface $response, 
                                   \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        // Sử dụng import - không dùng namespace trực tiếp
        $this->warehousesModel = new WarehousesModel();
    }

    public function isValidRole($role, $method, $segments)
    {
        return true;
    }

    public function getIndex()
    {
        // Lấy danh sách kho kèm theo thông tin quản lý
        $warehouses = $this->warehousesModel->getWarehousesWithManagers();
        $this->assign('warehouses', $warehouses);
        return $this->render();
    }

    public function getAdd()
    {
        return $this->render();
    }

    public function postAdd()
    {
        $name    = $this->request->getPost('name');
        $address = $this->request->getPost('address');

        if (empty($name) || empty($address)) {
            $this->session->setFlashdata('error', 'Vui lòng nhập đầy đủ thông tin');
            return redirect()->to('warehouse/add');
        }

        $data = [
            'name'       => $name,
            'address'    => $address,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $result = $this->warehousesModel->addWarehouseAndCategories($data);

        if ($result === false) {
            $this->session->setFlashdata('error', 'Thêm kho thất bại');
        } else {
            $this->session->setFlashdata('success', 'Thêm kho thành công');
        }

        return redirect()->to('warehouse');
    }
}
