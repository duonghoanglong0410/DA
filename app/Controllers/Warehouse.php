<?php namespace App\Controllers;

use App\Models\WarehousesModel;

class Warehouse extends BaseController
{
    private $warehousesModel;
    
    /**
     * Khởi tạo controller theo chuẩn CodeIgniter 4 (không dùng __construct)
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, 
                                   \CodeIgniter\HTTP\ResponseInterface $response, 
                                   \Psr\Log\LoggerInterface $logger)
    {
        // Phải gọi initController của parent trước
        parent::initController($request, $response, $logger);
        
        // Tải model cần thiết
        $this->warehousesModel = new WarehousesModel();
    }

    /**
     * Hàm kiểm tra quyền truy cập. (Mặc định trả về true)
     *
     * @param mixed $role
     * @param mixed $method
     * @param mixed $segments
     * @return bool
     */
    public function isValidRole($role, $method, $segments)
    {
        return true;
    }

    /**
     * Hiển thị danh sách kho.
     * Tên view: getIndex.php
     */
    public function getIndex()
    {
        $warehouses = $this->warehousesModel->findAll();
        $this->assign('warehouses', $warehouses);
        return $this->render();
    }

    /**
     * Hiển thị form thêm kho.
     * Tên view: getAdd.php
     */
    public function getAdd()
    {
        return $this->render();
    }

    /**
     * Xử lý thêm kho mới.
     * Khi thêm kho, sẽ gọi model để tự động thêm các bản ghi tương ứng
     * trong bảng warehouse_product_categories cho tất cả các product_categories hiện có.
     */
    public function postAdd()
    {
        // Lấy dữ liệu từ form
        $name    = $this->request->getPost('name');
        $address = $this->request->getPost('address');

        // Kiểm tra đầu vào
        if (empty($name) || empty($address)) {
            $this->session->setFlashdata('error', 'Vui lòng nhập đầy đủ thông tin');
            return redirect()->to('warehouse/add');
        }

        // Chuẩn bị dữ liệu cho kho
        $data = [
            'name'       => $name,
            'address'    => $address,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Gọi model để thêm kho và tự động cập nhật các bản ghi liên quan
        // Lưu ý: Phương thức này được định nghĩa trong WarehousesModel
        $result = $this->warehousesModel->addWarehouseAndCategories($data);

        if ($result === false) {
            $this->session->setFlashdata('error', 'Thêm kho thất bại');
        } else {
            $this->session->setFlashdata('success', 'Thêm kho thành công');
        }

        return redirect()->to('warehouse');
    }
}
