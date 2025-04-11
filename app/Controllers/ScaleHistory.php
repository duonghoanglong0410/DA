<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CanTuDongModel;    // Import Model vừa tạo
use App\Models\PurchaseYardModel; // Import Model bãi
use App\Models\ProductCategoryModel; // Import Model loại hàng

class ScaleHistory extends BaseController
{
    protected $canTuDongModel;
    protected $purchaseYardModel;
    protected $productCategoryModel;

    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);

        // Khởi tạo các model
        $this->canTuDongModel = new CanTuDongModel();
        $this->purchaseYardModel = new PurchaseYardModel();
        $this->productCategoryModel = new ProductCategoryModel();
    }

    // Hàm kiểm tra quyền (mặc định trả về true theo quy tắc)
    public function isValidRole($role, $method, $segments)
    {
        return true;
    }

    /**
     * Hiển thị danh sách lịch sử cân với bộ lọc và phân trang.
     */
    public function getIndex()
    {
        // Lấy tham số lọc và tìm kiếm từ URL (GET request)
        $filterParams = [
            'yard_id'    => $this->request->getGet('yard_id'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date'   => $this->request->getGet('end_date'),
            'chedo'      => $this->request->getGet('chedo'),
            'loaihang'   => $this->request->getGet('loaihang'),
        ];
        $searchTerm = $this->request->getGet('search_term') ?? '';

        // Xây dựng mảng $where cho Model dựa trên filterParams
        $where = [];
        if (!empty($filterParams['yard_id'])) {
            $where['can_tu_dong.purchase_yard_id'] = $filterParams['yard_id']; // Thêm tiền tố bảng
        }
        if (!empty($filterParams['start_date'])) {
            $where['can_tu_dong.Ngaycan >='] = $filterParams['start_date']; // Lớn hơn hoặc bằng
        }
        if (!empty($filterParams['end_date'])) {
            $where['can_tu_dong.Ngaycan <='] = $filterParams['end_date'];   // Nhỏ hơn hoặc bằng
        }
        if (!empty($filterParams['chedo'])) {
            $where['can_tu_dong.chedo'] = $filterParams['chedo'];
        }
        if (!empty($filterParams['loaihang'])) {
            // Sử dụng ILIKE hoặc LOWER() để tìm kiếm không phân biệt hoa thường
            $where['LOWER(can_tu_dong.Loaihang)'] = ['op' => 'like', 'val' => '%' . strtolower($filterParams['loaihang']) . '%'];
        }


        // Phân trang
        $totalRecords = $this->canTuDongModel->countScaleHistory($where, $searchTerm);
        $paginationData = $this->handlePagination($totalRecords); // Sử dụng hàm từ BaseController

        // Lấy dữ liệu đã phân trang
        $scaleHistory = $this->canTuDongModel->getPaginatedScaleHistory(
            $paginationData['perPage'],
            $paginationData['page'],
            'Ngaycan DESC', // Sắp xếp mặc định
            $where,
            $searchTerm
        );

        // Định dạng dữ liệu trước khi gửi ra view (theo quy tắc)
        foreach ($scaleHistory as $key => $item) {
            // Định dạng ngày tháng
            if (!empty($item['Ngaycan'])) {
                $scaleHistory[$key]['Ngaycan_formatted'] = date('d-m-Y', strtotime($item['Ngaycan']));
            } else {
                 $scaleHistory[$key]['Ngaycan_formatted'] = '';
            }
            // Định dạng các số
            $numericFields = ['KLcotai', 'KLkhongtai', 'KLhang', 'Dongia', 'KLgo', 'phantram', 'KLtru', 'Thanhtien', 'KLkhongtaiR', 'KLcotaiR', 'KLhangR'];
            foreach ($numericFields as $field) {
                if (isset($item[$field])) {
                    // Chuyển đổi tường minh thành float trước khi sử dụng number_format
                    $value = is_numeric($item[$field]) ? (float)$item[$field] : 0;
                    $scaleHistory[$key][$field . '_formatted'] = number_format($value, 0, ',', '.');
                } else {
                     $scaleHistory[$key][$field . '_formatted'] = '0'; // Hoặc giá trị mặc định khác
                }
            }
        }


        // Lấy danh sách bãi để lọc
        $yardsData = $this->purchaseYardModel->orderBy('yard_name', 'ASC')->findAll();
        
        // Xử lý dữ liệu yards để thêm thuộc tính selected
        $yards = [];
        foreach ($yardsData as $yard) {
            $selected = "";
            // Thêm thuộc tính selected cho option được chọn
            if ($yard['id'] == $filterParams['yard_id']) {
                $selected = "selected";
            }
            $yards[] = [
                'id' => $yard['id'],
                'yard_name' => $yard['yard_name'],
                'yard_code' => $yard['yard_code'],
                'selected' => $selected
            ];
        }
        
        // Tạo danh sách chế độ cân
        $cheDoOptions = [
            ['value' => '', 'name' => '-- Tất cả chế độ --', 'selected' => ($filterParams['chedo'] === '') ? 'selected' : ''],
            ['value' => 'Tự động/Auto', 'name' => 'Tự động', 'selected' => ($filterParams['chedo'] === 'Tự động/Auto') ? 'selected' : ''],
            ['value' => 'Bằng tay/Manual', 'name' => 'Bằng tay', 'selected' => ($filterParams['chedo'] === 'Bằng tay/Manual') ? 'selected' : '']
        ];
        
        // Lấy danh sách loại hàng từ bảng product_categories
        $categoriesData = $this->productCategoryModel->orderBy('name', 'ASC')->findAll();
        
        // Tạo các option cho dropdown loại hàng
        $loaiHangOptions = [];
        $loaiHangOptions[] = [
            'value' => '',
            'name' => '-- Tất cả loại hàng --',
            'selected' => ($filterParams['loaihang'] === '') ? 'selected' : ''
        ];
        
        foreach ($categoriesData as $category) {
            $selected = "";
            if (strcasecmp($category['name'], $filterParams['loaihang']) === 0) {
                $selected = "selected";
            }
            $loaiHangOptions[] = [
                'value' => $category['name'],
                'name' => $category['name'],
                'selected' => $selected
            ];
        }

        // Gán dữ liệu ra view
        $this->assign('scaleHistory', $scaleHistory);
        $this->assign('yards', $yards);               // Danh sách bãi cho bộ lọc đã xử lý selected
        $this->assign('cheDoOptions', $cheDoOptions); // Danh sách tùy chọn chế độ cân
        $this->assign('loaiHangOptions', $loaiHangOptions); // Danh sách loại hàng
        
        // Gán từng tham số lọc riêng lẻ thay vì gán cả mảng filterParams
        $this->assign('filter_yard_id', $filterParams['yard_id']);
        $this->assign('filter_start_date', $filterParams['start_date']);
        $this->assign('filter_end_date', $filterParams['end_date']);
        $this->assign('filter_chedo', $filterParams['chedo']);
        $this->assign('filter_loaihang', $filterParams['loaihang']);
        
        $this->assign('searchTerm', $searchTerm);     // Giá trị tìm kiếm hiện tại
        // Pagination đã được gán tự động bởi handlePagination thông qua $this->assign('pagination', ...)

        // Gán biến array cho thông báo "không có dữ liệu" thay vì dùng {if} trong view
        if (empty($scaleHistory)) {
            $this->assign('noDataMessage', [['message' => 'Không tìm thấy dữ liệu phù hợp.']]);
        } else {
            $this->assign('noDataMessage', []); // Gán mảng rỗng nếu có dữ liệu
        }

        // Render view (tên file view phải là index.php theo quy tắc)
        return $this->render();
    }
}
