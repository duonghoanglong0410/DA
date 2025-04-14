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
            'phieu_type' => $this->request->getGet('phieu_type'),
        ];
        $searchTerm = $this->request->getGet('search_term') ?? '';

        // Xây dựng mảng $where cho Model dựa trên filterParams
        $where = $this->buildWhereConditions($filterParams);

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

        // Chuẩn bị dữ liệu cho form lọc
        $this->prepareFilterData($filterParams);

        // Gán dữ liệu ra view
        $this->assign('scaleHistory', $scaleHistory);
        
        // Gán từng tham số lọc riêng lẻ thay vì gán cả mảng filterParams
        $this->assign('filter_yard_id', $filterParams['yard_id']);
        $this->assign('filter_start_date', $filterParams['start_date']);
        $this->assign('filter_end_date', $filterParams['end_date']);
        $this->assign('filter_chedo', $filterParams['chedo']);
        $this->assign('filter_loaihang', $filterParams['loaihang']);
        $this->assign('filter_phieu_type', $filterParams['phieu_type']);
        
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
    
    /**
     * Hiển thị thống kê dữ liệu cân theo bãi và loại hàng
     */
    public function getStats()
    {
        // Lấy tham số lọc và tìm kiếm từ URL (GET request), giống như phương thức getIndex
        $filterParams = [
            'yard_id'    => $this->request->getGet('yard_id'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date'   => $this->request->getGet('end_date'),
            'chedo'      => $this->request->getGet('chedo'),
            'loaihang'   => $this->request->getGet('loaihang'),
            'phieu_type' => $this->request->getGet('phieu_type'),
        ];
        $searchTerm = $this->request->getGet('search_term') ?? '';

        // Xây dựng mảng $where cho Model dựa trên filterParams
        $where = $this->buildWhereConditions($filterParams);

        // Lấy dữ liệu thống kê
        $statsData = $this->canTuDongModel->getStatsByYardAndType($where, $searchTerm);
        
        // Định dạng số liệu
        foreach ($statsData as $key => $stat) {
            $numericFields = ['total_KLkhongtai', 'total_KLcotai', 'total_KLhang', 'total_Thanhtien'];
            foreach ($numericFields as $field) {
                $statsData[$key][$field . '_formatted'] = number_format((float)$stat[$field], 0, ',', '.');
            }
        }
        
        // Xử lý rowspan cho bãi và loại hàng
        $processedData = $this->processStatsForRowspan($statsData);
        
        // Chuẩn bị dữ liệu cho form lọc
        $this->prepareFilterData($filterParams);
        
        // Gán dữ liệu ra view
        $this->assign('statsData', $processedData);
        
        // Gán từng tham số lọc riêng lẻ
        $this->assign('filter_yard_id', $filterParams['yard_id']);
        $this->assign('filter_start_date', $filterParams['start_date']);
        $this->assign('filter_end_date', $filterParams['end_date']);
        $this->assign('filter_chedo', $filterParams['chedo']);
        $this->assign('filter_loaihang', $filterParams['loaihang']);
        $this->assign('filter_phieu_type', $filterParams['phieu_type']);
        
        $this->assign('searchTerm', $searchTerm);
        
        // Gán biến array cho thông báo "không có dữ liệu" thay vì dùng {if} trong view
        if (empty($statsData)) {
            $this->assign('noDataMessage', [['message' => 'Không tìm thấy dữ liệu phù hợp cho thống kê.']]);
        } else {
            $this->assign('noDataMessage', []); // Gán mảng rỗng nếu có dữ liệu
        }
        
        // Render view
        return $this->render();
    }
    
    /**
     * Hàm helper để xây dựng điều kiện where từ filter params
     */
    private function buildWhereConditions($filterParams)
    {
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
        if (!empty($filterParams['phieu_type'])) {
            // Lọc theo loại phiếu (Msp), ví dụ: NK123456, CT123456, XK123456
            $where['can_tu_dong.Msp'] = ['op' => 'like', 'val' => $filterParams['phieu_type'] . '%'];
        }
        
        return $where;
    }
    
    /**
     * Hàm helper để chuẩn bị dữ liệu cho form lọc
     */
    private function prepareFilterData($filterParams)
    {
        // Lấy danh sách bãi
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
        
        // Tạo danh sách loại phiếu
        $phieuTypeOptions = [
            ['value' => '', 'name' => '-- Tất cả loại phiếu --', 'selected' => ($filterParams['phieu_type'] === '') ? 'selected' : ''],
            ['value' => 'NK', 'name' => 'NK - Nhập kho', 'selected' => ($filterParams['phieu_type'] === 'NK') ? 'selected' : ''],
            ['value' => 'CT', 'name' => 'CT - Chuyển tiếp', 'selected' => ($filterParams['phieu_type'] === 'CT') ? 'selected' : ''],
            ['value' => 'XK', 'name' => 'XK - Xuất kho', 'selected' => ($filterParams['phieu_type'] === 'XK') ? 'selected' : '']
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
        $this->assign('yards', $yards);               // Danh sách bãi cho bộ lọc đã xử lý selected
        $this->assign('cheDoOptions', $cheDoOptions); // Danh sách tùy chọn chế độ cân
        $this->assign('loaiHangOptions', $loaiHangOptions); // Danh sách loại hàng
        $this->assign('phieuTypeOptions', $phieuTypeOptions); // Danh sách loại phiếu
    }
    
    /**
     * Xử lý dữ liệu thống kê để tạo rowspan cho các ô cần merge
     * 
     * @param array $statsData Dữ liệu thống kê gốc
     * @return array Dữ liệu đã xử lý với các thuộc tính rowspan
     */
    private function processStatsForRowspan($statsData)
    {
        if (empty($statsData)) {
            return [];
        }
        
        // Tạo mảng để đếm số lượng dòng cho mỗi bãi và loại hàng
        $yardCount = [];
        $loaiHangCount = [];
        
        // Đếm số lượng dòng cho mỗi bãi và loại hàng
        foreach ($statsData as $row) {
            $yardKey = $row['yard_code'] . '-' . $row['yard_name'];
            if (!isset($yardCount[$yardKey])) {
                $yardCount[$yardKey] = 0;
            }
            $yardCount[$yardKey]++;
            
            // Đếm số lượng dòng cho mỗi loại hàng trong một bãi
            $loaiHangKey = $yardKey . '-' . $row['Loaihang'];
            if (!isset($loaiHangCount[$loaiHangKey])) {
                $loaiHangCount[$loaiHangKey] = 0;
            }
            $loaiHangCount[$loaiHangKey]++;
        }
        
        // Tạo cấu trúc dữ liệu mới
        $processedData = [];
        $currentYardKey = null;
        $currentLoaiHangKey = null;
        
        foreach ($statsData as $index => $row) {
            $yardKey = $row['yard_code'] . '-' . $row['yard_name'];
            $loaiHangKey = $yardKey . '-' . $row['Loaihang'];
            
            // Bản ghi cơ bản
            $newRow = [
                'Msp' => $row['Msp'],
                'total_records' => $row['total_records'],
                'total_KLkhongtai' => $row['total_KLkhongtai'],
                'total_KLcotai' => $row['total_KLcotai'],
                'total_KLhang' => $row['total_KLhang'],
                'total_Thanhtien' => $row['total_Thanhtien'],
                'total_KLkhongtai_formatted' => $row['total_KLkhongtai_formatted'],
                'total_KLcotai_formatted' => $row['total_KLcotai_formatted'],
                'total_KLhang_formatted' => $row['total_KLhang_formatted'],
                'total_Thanhtien_formatted' => $row['total_Thanhtien_formatted'],
                'is_new_yard' => [], // Mặc định không phải dòng đầu của bãi mới (mảng rỗng)
                'is_not_new_yard' => [[]], // Mặc định là không phải dòng đầu bãi mới (mảng có phần tử)
            ];
            
            // Tạo biến array thể hiện điều kiện hiển thị
            $canDisplayYard = [];  
            $canDisplayLoaihang = [];  

            // Hàng đầu tiên của bãi
            if ($currentYardKey !== $yardKey) {
                $canDisplayYard = [[]]; // Hiển thị
                $currentYardKey = $yardKey;
                $currentLoaiHangKey = null; // Reset khi chuyển bãi
                $newRow['is_new_yard'] = [[]]; // Đánh dấu là dòng đầu của bãi mới (mảng có phần tử)
                $newRow['is_not_new_yard'] = []; // Không phải dòng thường (mảng rỗng để ẩn)
            }
            
            // Hàng đầu tiên của loại hàng trong bãi
            if ($currentLoaiHangKey !== $loaiHangKey) {
                $canDisplayLoaihang = [[]]; // Hiển thị
                $currentLoaiHangKey = $loaiHangKey;
            }
            
            // Bổ sung dữ liệu bãi
            if (!empty($canDisplayYard)) {
                $newRow['can_display_yard'] = $canDisplayYard;
                $newRow['yard_info'] = [
                    [
                        'yard_code' => $row['yard_code'],
                        'yard_name' => $row['yard_name'],
                        'rowspan' => $yardCount[$yardKey]
                    ]
                ];
            } else {
                $newRow['can_display_yard'] = [];
                $newRow['yard_info'] = [];
            }
            
            // Bổ sung dữ liệu loại hàng
            if (!empty($canDisplayLoaihang)) {
                $newRow['can_display_loaihang'] = $canDisplayLoaihang;
                $newRow['loaihang_info'] = [
                    [
                        'Loaihang' => $row['Loaihang'],
                        'rowspan' => $loaiHangCount[$loaiHangKey]
                    ]
                ];
            } else {
                $newRow['can_display_loaihang'] = [];
                $newRow['loaihang_info'] = [];
            }
            
            $processedData[] = $newRow;
        }
        
        return $processedData;
    }


}
