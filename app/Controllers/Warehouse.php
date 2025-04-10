<?php namespace App\Controllers;

use App\Models\WarehousesModel;
use App\Models\UserRoleAssignmentModel;

class Warehouse extends BaseController
{
    // Khai báo property cho model
    protected $warehousesModel;
    protected $userRoleAssignmentModel;
    // Giả sử role_id cho người quản lý kho là 4, có thể thay đổi theo cấu hình thực tế
    protected $roleWarehouseManager = 4;

    /**
     * Khởi tạo controller theo chuẩn CodeIgniter 4 (không sử dụng __construct)
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        // Sử dụng import (use) thay vì ghi trực tiếp namespace
        $this->warehousesModel = new WarehousesModel();
        $this->userRoleAssignmentModel = new UserRoleAssignmentModel();
    }

    /**
     * Kiểm tra quyền truy cập. Mặc định trả về true.
     */
    public function isValidRole($role, $method, $segments)
    {
        return true;
    }

    /**
     * Hiển thị danh sách kho kèm theo thông tin quản lý.
     * View: getIndex.php
     */
    public function getIndex()
    {
        // Tính tổng số kho
        $totalWarehouses = $this->warehousesModel->countAll();

        // Sử dụng phương thức handlePagination từ BaseController
        $pagination = $this->handlePagination($totalWarehouses);

        // Lấy danh sách kho đã phân trang
        $warehouses = $this->warehousesModel->customPaginate($pagination['perPage'], $pagination['page']);

        // Gán dữ liệu vào view
        $this->assign('warehouses', $warehouses);        
        return $this->render();
    }

    /**
     * Hiển thị form thêm kho mới.
     * View: getAdd.php
     */
    public function getAdd()
    {
        return $this->render();
    }

    /**
     * Xử lý thêm kho mới.
     * Gọi model để tự động tạo các bản ghi liên quan trong bảng warehouse_product_categories.
     */
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

    /**
     * Hiển thị form sửa kho.
     * View: getEdit.php
     *
     * @param int $warehouseId ID của kho cần sửa.
     */
    public function getEdit($warehouseId = null)
    {
        if (empty($warehouseId)) {
            $this->session->setFlashdata('error', 'Không xác định được kho cần sửa');
            return redirect()->to('warehouse');
        }

        $warehouse = $this->warehousesModel->find($warehouseId);
        if (!$warehouse) {
            $this->session->setFlashdata('error', 'Kho không tồn tại');
            return redirect()->to('warehouse');
        }

        // Gán từng thuộc tính của record theo quy tắc riêng lẻ
        $this->assign('id', $warehouse['id']);
        $this->assign('name', $warehouse['name']);
        $this->assign('address', $warehouse['address']);

        // Lấy danh sách user_id của những người quản lý hiện tại của kho đó
        $managerIds = $this->userRoleAssignmentModel->getManagerIdsByWarehouse($warehouseId, $this->roleWarehouseManager);
        // Gán thành chuỗi các id, phân cách bằng dấu phẩy (ví dụ: "1,3,5")
        $this->assign('manager_ids', implode(',', $managerIds));

        return $this->render();
    }

    /**
     * Xử lý cập nhật thông tin kho.
     * Sau khi cập nhật thông tin kho, cập nhật các thông tin phân quyền cho user_role_assignments:
     * - Xóa các bản ghi cũ của kho này cho role quản lý.
     * - Thêm các bản ghi mới dựa trên mảng người quản lý được gửi lên.
     *
     * @param int $warehouseId ID của kho cần cập nhật.
     */
    public function postEdit($warehouseId = null)
    {
        if (empty($warehouseId)) {
            $this->session->setFlashdata('error', 'Không xác định được kho cần sửa');
            return redirect()->to('warehouse');
        }
    
        $name    = $this->request->getPost('name');
        $address = $this->request->getPost('address');
        // Lấy mảng người quản lý được gửi lên (dạng array, key managers)
        $newManagers = $this->request->getPost('managers');
    
        if (empty($name) || empty($address)) {
            $this->session->setFlashdata('error', 'Vui lòng nhập đầy đủ thông tin');
            return redirect()->to("warehouse/edit/{$warehouseId}");
        }
    
        $data = [
            'name'       => $name,
            'address'    => $address,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    
        // Cập nhật thông tin kho
        $result = $this->warehousesModel->update($warehouseId, $data);
        if ($result === false) {
            $this->session->setFlashdata('error', 'Cập nhật kho thất bại');
            return redirect()->to("warehouse/edit/{$warehouseId}");
        } else {
            // Lấy danh sách manager cũ của kho, theo role quản lý (role id đã được định nghĩa)
            $oldManagers = $this->userRoleAssignmentModel->getManagerIdsByWarehouse($warehouseId, $this->roleWarehouseManager);
            if (!is_array($newManagers)) {
                $newManagers = [];
            }
            // Tính toán: những manager cần xóa = có trong old nhưng không có trong new
            $managersToDelete = array_diff($oldManagers, $newManagers);
            // Tính toán: những manager cần thêm = có trong new nhưng không có trong old
            $managersToAdd = array_diff($newManagers, $oldManagers);
    
            // Xóa các bản ghi không còn được chọn
            if (!empty($managersToDelete)) {
                $this->userRoleAssignmentModel->deleteByWarehouseAndRoleWithUserIds($warehouseId, $this->roleWarehouseManager, $managersToDelete);
            }
    
            // Thêm các bản ghi mới nếu có
            if (!empty($managersToAdd)) {
                foreach ($managersToAdd as $managerId) {
                    $assignmentData = [
                        'user_id'      => $managerId,
                        'role_id'      => $this->roleWarehouseManager,
                        'warehouse_id' => $warehouseId,
                        'created_at'   => date('Y-m-d H:i:s'),
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ];
                    $this->userRoleAssignmentModel->insert($assignmentData);
                }
            }
    
            $this->session->setFlashdata('success', 'Cập nhật kho thành công');
        }
    
        return redirect()->to('warehouse');
    }
    

    /**
     * Xử lý xoá kho.
     * Xoá kho chỉ thực hiện khi điều kiện cho phép được thỏa (điều kiện kiểm tra trong model).
     *
     * @param int $warehouseId ID của kho cần xoá.
     */
    public function getDelete($warehouseId = null)
    {
        if (empty($warehouseId)) {
            $this->session->setFlashdata('error', 'Không xác định được kho cần xoá');
            return redirect()->to('warehouse');
        }

        if (!$this->warehousesModel->canDeleteWarehouse($warehouseId)) {
            $this->session->setFlashdata('error', 'Không thể xoá kho: tồn tại số lượng hàng tồn khác 0 hoặc kho đang được sử dụng trong giao dịch');
            return redirect()->to('warehouse');
        }

        $result = $this->warehousesModel->delete($warehouseId);
        if ($result === false) {
            $this->session->setFlashdata('error', 'Xoá kho thất bại');
        } else {
            $this->session->setFlashdata('success', 'Xoá kho thành công');
        }
        return redirect()->to('warehouse');
    }
}
