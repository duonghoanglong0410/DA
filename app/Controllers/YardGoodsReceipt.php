<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CanTuDongModel;
use App\Models\ProductCategoryModel;
use App\Models\PurchaseYardModel;
use App\Models\PurchaseYardGoodsReceiptModel;
use App\Models\PurchaseYardProductInfoModel;
use App\Models\CurrencyModel;
use App\Models\UserRoleAssignmentModel;
use App\Constants\Constants;
use App\Models\ReceiptAdjustmentModel;

class YardGoodsReceipt extends BaseController
{
    protected $canTuDongModel;
    protected $productCategoryModel;
    protected $purchaseYardModel;
    protected $purchaseYardGoodsReceiptModel;
    protected $purchaseYardProductInfoModel;
    protected $currencyModel;
    protected $userRoleAssignmentModel;
    protected $receiptAdjustmentModel;
    
    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Khởi tạo các model
        $this->canTuDongModel = new CanTuDongModel();
        $this->productCategoryModel = new ProductCategoryModel();
        $this->purchaseYardModel = new PurchaseYardModel();
        $this->purchaseYardGoodsReceiptModel = new PurchaseYardGoodsReceiptModel();
        $this->purchaseYardProductInfoModel = new PurchaseYardProductInfoModel();
        $this->currencyModel = new CurrencyModel();
        $this->userRoleAssignmentModel = new UserRoleAssignmentModel();
        $this->receiptAdjustmentModel = new ReceiptAdjustmentModel();
    }
    
    // Hàm kiểm tra quyền (mặc định trả về true theo quy tắc)
    public function isValidRole($role, $method, $segments)
    {
        return true;
    }
    
    /**
     * Hiển thị trang lập phiếu cân nhập hàng
     */
    public function getIndex()
    {
        // Lấy ngày hiện tại
        $today = date('Y-m-d');
        
        // Lấy user_id từ session
        $userId = $this->session->userId;
        
        // Lấy danh sách bãi được phân quyền cho user
        $authorizedYards = $this->userRoleAssignmentModel->getAuthorizedYardsByUserId($userId);
        
        if (empty($authorizedYards)) {
            $this->session->setFlashdata('error', 'Bạn không có quyền truy cập vào bất kỳ bãi nào.');
            $this->assign('today_can_data', []);
            $this->assign('products', []);
            $this->assign('currencies', []);
            $this->assign('authorized_yards', []);
            return $this->render();
        }
        
        // Lấy danh sách yard_id
        $yardIds = array_column($authorizedYards, 'purchase_yard_id');
        
        // Lấy dữ liệu CAN_TU_DONG của ngày hiện tại với Msp bắt đầu bằng NK và thuộc bãi được phân quyền
        $canData = $this->canTuDongModel->getTodayReceiptDataByYardIds($today, $yardIds, 'NK');
        
        // Format dữ liệu
        foreach ($canData as $key => $item) {
            $canData[$key]['KLhang_formatted'] = number_format($item['KLhang'], 0, ',', '.');
            $canData[$key]['Dongia_formatted'] = number_format($item['Dongia'], 0, ',', '.');
            $canData[$key]['Thanhtien_formatted'] = number_format($item['Thanhtien'], 0, ',', '.');
            // Tạo mã hash cho javascript unique ID
            $canData[$key]['row_id'] = 'row_' . md5($item['id']);
        }
        
        // Kiểm tra và gán biến hiển thị thông báo khi không có dữ liệu
        $noDataMessage = empty($canData) ? [['message' => 'Không có dữ liệu phiếu cân NK trong ngày hôm nay.']] : [];
        $this->assign('no_data_message', $noDataMessage);
        
        // Lấy danh sách loại mặt hàng từ purchase_yard_product_info join với product_categories
        // Chỉ lấy các loại mặt hàng được gắn với bãi
        $products = $this->purchaseYardProductInfoModel->getProductCategoriesByYardIds($yardIds);
        
        // Kiểm tra số lượng loại mặt hàng
        $singleProductCategory = (count($products) === 1);
        $this->assign('single_product_category', $singleProductCategory ? 'true' : 'false');
        if ($singleProductCategory && !empty($products)) {
            // Tách các giá trị thành các biến riêng biệt thay vì gán cả array
            $this->assign('auto_selected_category_id', $products[0]['id']);
            $this->assign('auto_selected_category_name', $products[0]['name']);
            $this->assign('auto_selected_category_abbreviation', $products[0]['abbreviation']);
        } else {
            $this->assign('auto_selected_category_id', '');
            $this->assign('auto_selected_category_name', '');
            $this->assign('auto_selected_category_abbreviation', '');
        }
        
        // Lấy danh sách loại tiền tệ từ purchase_yard_product_info
        $yardCurrencies = [];
        $allYardCurrencies = []; // Tập hợp tất cả các loại tiền tệ được sử dụng trong các bãi
        
        foreach ($authorizedYards as $yard) {
            $yardId = $yard['purchase_yard_id'];
            // Lấy các loại tiền tệ thực tế đang được sử dụng trong kho của bãi
            $currenciesForYard = $this->purchaseYardProductInfoModel->getCurrenciesByYardId($yardId);
                
            $currencyIds = [];
            foreach ($currenciesForYard as $row) {
                $currencyIds[] = $row['currency_id'];
                if (!in_array($row['currency_id'], $allYardCurrencies)) {
                    $allYardCurrencies[] = $row['currency_id'];
                }
            }
            
            if (!empty($currencyIds)) {
                $currencies = $this->currencyModel->whereIn('id', $currencyIds)->findAll();
                $yardCurrencies[$yardId] = $currencies;
            } else {
                $yardCurrencies[$yardId] = [];
            }
        }
        
        // Lấy chi tiết của các loại tiền tệ được sử dụng
        $currencies = [];
        if (!empty($allYardCurrencies)) {
            $currencies = $this->currencyModel->whereIn('id', $allYardCurrencies)->findAll();
        }
        
        // JSON encode yardCurrencies để sử dụng trong JavaScript
        $yardCurrenciesJson = json_encode($yardCurrencies);
        
        // Gán dữ liệu ra view
        $this->assign('today_can_data', $canData);
        $this->assign('products', $products);
        $this->assign('currencies', $currencies);
        $this->assign('yard_currencies_json', $yardCurrenciesJson);
        $this->assign('authorized_yards', $authorizedYards);
        $this->assign('current_date', date('d-m-Y'));
        
        return $this->render();
    }
    
    /**
     * Xử lý lưu phiếu nhập hàng
     */
    public function postSave()
    {
        // Lấy dữ liệu từ request
        $selectedItems = $this->request->getPost('selected_items');
        $yard_id = $this->request->getPost('yard_id');
        $category_id = $this->request->getPost('category_id');
        $vehicle_number = $this->request->getPost('vehicle_number');
        $quantity = $this->request->getPost('quantity');
        $unit_price = $this->request->getPost('unit_price');
        $currency_id = $this->request->getPost('currency_id') ?? null;
        
        // Kiểm tra dữ liệu đầu vào
        if (empty($yard_id) || empty($category_id) || empty($quantity) || empty($unit_price)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!'
            ]);
        }
        
        // Lấy thông tin sản phẩm hiện tại trong bãi
        $productInfo = $this->purchaseYardProductInfoModel->getProductInfoByYardAndCategory($yard_id, $category_id, $currency_id);
        
        if (empty($productInfo)) {
            // Tạo mới nếu chưa có
            $productInfo = [
                'purchase_yard_id' => $yard_id,
                'category_id' => $category_id,
                'currency_id' => $currency_id,
                'stock_weight' => 0,
                'average_price' => 0
            ];
        }
        
        // Tính giá trung bình mới
        $oldValue = $productInfo['stock_weight'] * $productInfo['average_price'];
        $newValue = $quantity * $unit_price;
        $newTotalWeight = $productInfo['stock_weight'] + $quantity;
        $newAveragePrice = 0;
        
        if ($newTotalWeight > 0) {
            $newAveragePrice = ($oldValue + $newValue) / $newTotalWeight;
        }
        
        // Bắt đầu transaction
        $this->purchaseYardGoodsReceiptModel->beginTransaction();
        
        try {
            // Lưu phiếu nhập hàng
            $receiptData = [
                'purchase_yard_id' => $yard_id,
                'vehicle_number' => $vehicle_number,
                'category_id' => $category_id,
                'weight' => $quantity,
                'unit_price' => $unit_price,
                'created_by' => $this->session->userId
            ];
            
            $receipt_id = $this->purchaseYardGoodsReceiptModel->insert($receiptData);

            if (empty($receipt_id)) {
                $this->purchaseYardGoodsReceiptModel->rollbackTransaction();

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Lỗi khi lưu phiếu nhập hàng!'
                ]);
            }
            
            // Cập nhật thông tin sản phẩm
            if (isset($productInfo['id'])) {
                // Cập nhật nếu đã có
                $this->purchaseYardProductInfoModel->update($productInfo['id'], [
                    'stock_weight' => $newTotalWeight,
                    'average_price' => $newAveragePrice
                ]);
            } else {
                // Tạo mới nếu chưa có
                $this->purchaseYardProductInfoModel->insert([
                    'purchase_yard_id' => $yard_id,
                    'category_id' => $category_id,
                    'currency_id' => $currency_id,
                    'stock_weight' => $newTotalWeight,
                    'average_price' => $newAveragePrice
                ]);
            }
            
            // Cập nhật trạng thái đã lập phiếu cho các phiếu cân đã chọn
            if (!empty($selectedItems)) {            
                $this->canTuDongModel->markAsReceipted($selectedItems);                                
            }
            
            // Commit transaction
            $this->purchaseYardGoodsReceiptModel->commitTransaction();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Phiếu nhập hàng đã được lưu thành công!',
                'receipt_id' => $receipt_id
            ]);
            
        } catch (\Exception $e) {
            $this->purchaseYardGoodsReceiptModel->rollbackTransaction();
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Hiển thị lịch sử phiếu nhập hàng trong N ngày gần đây
     */
    public function getHistory()
    {
        // Lấy user_id từ session
        $userId = $this->session->userId;
        
        // Lấy danh sách bãi được phân quyền cho user
        $authorizedYards = $this->userRoleAssignmentModel->getAuthorizedYardsByUserId($userId);
        
        if (empty($authorizedYards)) {
            $this->session->setFlashdata('error', 'Bạn không có quyền truy cập vào bất kỳ bãi nào.');
            $this->assign('receipts', []);
            $this->assign('yard_options', []);
            $this->assign('category_options', []);
            $this->assign('no_data_message', [['message' => 'Bạn không có quyền truy cập vào bất kỳ bãi nào.']]);
            return $this->render();
        }
        
        // Lấy danh sách yard_id
        $yardIds = array_column($authorizedYards, 'purchase_yard_id');
        
        // Lấy số ngày lịch sử từ Constants
        $historyDays = Constants::RECEIPT_HISTORY_DAYS;
        
        // Tính ngày bắt đầu (N ngày trước)
        $startDate = date('Y-m-d', strtotime('-' . $historyDays . ' days'));
        $endDate = date('Y-m-d');
        
        // Lấy danh sách phiếu nhập hàng từ N ngày trước đến hiện tại
        $receipts = $this->purchaseYardGoodsReceiptModel->getReceiptsWithinDateRange($startDate, $endDate, $yardIds);
        
        // Lấy thông tin các bãi
        $yards = $this->purchaseYardModel->whereIn('id', $yardIds)->findAll();
        $yardsById = [];
        foreach ($yards as $yard) {
            $yardsById[$yard['id']] = $yard;
        }
        
        // Lấy thông tin các loại sản phẩm
        $categoryIds = array_unique(array_column($receipts, 'category_id'));
        $categories = [];
        if (!empty($categoryIds)) {
            $categories = $this->productCategoryModel->whereIn('id', $categoryIds)->findAll();
        }
        $categoriesById = [];
        foreach ($categories as $category) {
            $categoriesById[$category['id']] = $category;
        }
        
        // Xác định có nhiều bãi hay không
        $hasMultipleYards = count($yards) > 1;
        
        // Format dữ liệu
        $formattedReceipts = [];
        foreach ($receipts as $receipt) {
            $yardName = isset($yardsById[$receipt['purchase_yard_id']]) ? $yardsById[$receipt['purchase_yard_id']]['yard_name'] . ' (' . $yardsById[$receipt['purchase_yard_id']]['yard_code'] . ')' : 'N/A';
            $yardColumnBody = $hasMultipleYards ? '<td data-yard-id="' . $receipt['purchase_yard_id'] . '">' . $yardName . '</td>' : '';
            
            $formattedReceipts[] = [
                'id' => $receipt['id'],
                'input_date' => date('d-m-Y', strtotime($receipt['created_at'])),
                'yard_id' => $receipt['purchase_yard_id'],
                'yard_name' => $yardName,
                'yard_column_body' => $yardColumnBody,
                'vehicle_number' => $receipt['vehicle_number'],
                'category_id' => $receipt['category_id'],
                'category_name' => isset($categoriesById[$receipt['category_id']]) ? $categoriesById[$receipt['category_id']]['name'] : 'N/A',
                'quantity' => $receipt['weight'],
                'quantity_formatted' => number_format($receipt['weight'], 0, ',', '.'),
                'unit_price' => $receipt['unit_price'],
                'unit_price_formatted' => number_format($receipt['unit_price'], 0, ',', '.'),
                'total_amount' => $receipt['weight'] * $receipt['unit_price'],
                'total_amount_formatted' => number_format($receipt['weight'] * $receipt['unit_price'], 0, ',', '.'),
                'creator_name' => $receipt['creator_name'] ?? 'N/A'
            ];
        }
        
        // Kiểm tra và gán biến hiển thị thông báo khi không có dữ liệu
        $noDataMessage = empty($formattedReceipts) ? [['message' => 'Không có dữ liệu phiếu nhập hàng trong ' . $historyDays . ' ngày qua.']] : [];
        
        // Chuẩn bị dữ liệu cho dropdown lọc
        $yardOptions = [];
        foreach ($yards as $yard) {
            $yardOptions[] = [
                'id' => $yard['id'],
                'yard_name' => $yard['yard_name'] . ' (' . $yard['yard_code'] . ')'
            ];
        }
        
        $categoryOptions = [];
        foreach ($categories as $category) {
            $categoryOptions[] = [
                'id' => $category['id'],
                'name' => $category['name']
            ];
        }
        
        // Xác định có nhiều bãi hay không
        $hasMultipleYards = count($yards) > 1;
        
        // Xác định số cột và chiều rộng cột cho bộ lọc
        $colspanCount = $hasMultipleYards ? 8 : 7; // Số cột khi có/không có cột bãi (including the Actions column)
        $yardFilterColumnWidth = $hasMultipleYards ? 6 : 12; // Chiều rộng cột lọc loại hàng
        
        // Chuẩn bị HTML cho yard filter (chỉ hiển thị khi có nhiều bãi)
        $yardFilterHtml = '';
        if ($hasMultipleYards) {
            $yardFilterHtml = '<div class="col-md-6 mb-3"><div class="form-group">';
            $yardFilterHtml .= '<label for="filter_yard" class="form-label">Lọc theo bãi:</label>';
            $yardFilterHtml .= '<select id="filter_yard" class="form-select">';
            $yardFilterHtml .= '<option value="all">Tất cả</option>';
            
            foreach ($yardOptions as $yard) {
                $yardFilterHtml .= '<option value="' . $yard['id'] . '">' . $yard['yard_name'] . '</option>';
            }
            
            $yardFilterHtml .= '</select></div></div>';
        }
        
        // Chuẩn bị HTML cho yard column trong table header
        $yardColumnHeaderHtml = $hasMultipleYards ? '<th>Bãi</th>' : '';
        
        // Gán dữ liệu ra view
        $this->assign('receipts', $formattedReceipts);
        $this->assign('category_options', $categoryOptions);
        $this->assign('no_data_message', $noDataMessage);
        $this->assign('history_days', $historyDays);
        $this->assign('yard_filter_html', $yardFilterHtml);
        $this->assign('yard_column_header', $yardColumnHeaderHtml);
        $this->assign('colspan_count', $colspanCount);
        $this->assign('yard_filter_column_width', $yardFilterColumnWidth);
        
        return $this->render();
    }
    
    /**
     * Hiển thị form tạo đề nghị chỉnh sửa phiếu nhập hàng
     */
    public function getCreateAdjustment($receiptId = null)
    {
        if (empty($receiptId)) {
            $this->session->setFlashdata('error', 'Không tìm thấy phiếu nhập hàng.');
            return redirect()->to(base_url('/yard-goods-receipt/history'));
        }
        
        // Lấy thông tin phiếu cần chỉnh sửa
        $receipt = $this->purchaseYardGoodsReceiptModel->find($receiptId);
        
        if (empty($receipt)) {
            $this->session->setFlashdata('error', 'Không tìm thấy phiếu nhập hàng.');
            return redirect()->to(base_url('/yard-goods-receipt/history'));
        }
        
        // Kiểm tra xem có tồn tại phiếu điều chỉnh chưa được duyệt cho phiếu nhập này không
        $existingAdjustment = $this->receiptAdjustmentModel->where([
            'receipt_id' => $receiptId,
            'reviewed_by' => 0
        ])->first();
        
        // Lấy thông tin chi tiết phiếu
        $yard = $this->purchaseYardModel->find($receipt['purchase_yard_id']);
        $category = $this->productCategoryModel->find($receipt['category_id']);
        
        // Gán từng thuộc tính ra view riêng lẻ thay vì dùng object
        $this->assign('receipt_id', $receipt['id']);
        $this->assign('receipt_input_date', date('d-m-Y', strtotime($receipt['created_at'])));
        $this->assign('receipt_yard_id', $receipt['purchase_yard_id']);
        $this->assign('receipt_yard_name', $yard ? $yard['yard_name'] . ' (' . $yard['yard_code'] . ')' : 'N/A');
        $this->assign('receipt_vehicle_number', $receipt['vehicle_number']);
        $this->assign('receipt_category_id', $receipt['category_id']);
        $this->assign('receipt_category_name', $category ? $category['name'] : 'N/A');
        
        // Thiết lập giá trị khối lượng và đơn giá
        if ($existingAdjustment) {
            // Nếu đã có phiếu điều chỉnh chưa được duyệt, hiển thị giá trị từ phiếu đó
            $this->assign('receipt_weight', round($receipt['weight']));
            $this->assign('receipt_weight_formatted', number_format(round($receipt['weight']), 0, ',', '.'));
            $this->assign('receipt_unit_price', round($receipt['unit_price']));
            $this->assign('receipt_unit_price_formatted', number_format(round($receipt['unit_price']), 0, ',', '.'));
            
            // Tính tổng tiền ban đầu
            $roundedTotal = round($receipt['weight']) * round($receipt['unit_price']);
            $this->assign('receipt_total_amount', $roundedTotal);
            $this->assign('receipt_total_amount_formatted', number_format($roundedTotal, 0, ',', '.'));
            
            // Giá trị mới đề xuất từ phiếu điều chỉnh đã có
            $this->assign('new_weight', round($existingAdjustment['new_weight']));
            $this->assign('new_unit_price', round($existingAdjustment['new_unit_price']));
            
            // Thông báo cho người dùng biết đang chỉnh sửa phiếu điều chỉnh đã tồn tại
            $this->assign('is_editing_existing', [[1]]);
            $this->assign('adjustment_id', $existingAdjustment['id']);
            $this->session->setFlashdata('info', 'Đang chỉnh sửa phiếu điều chỉnh đã tạo trước đó chưa được duyệt.');
        } else {
            // Nếu chưa có phiếu điều chỉnh, sử dụng giá trị gốc
            $this->assign('receipt_weight', round($receipt['weight']));
            $this->assign('receipt_weight_formatted', number_format(round($receipt['weight']), 0, ',', '.'));
            $this->assign('receipt_unit_price', round($receipt['unit_price']));
            $this->assign('receipt_unit_price_formatted', number_format(round($receipt['unit_price']), 0, ',', '.'));
            
            // Tính tổng tiền ban đầu
            $roundedTotal = round($receipt['weight']) * round($receipt['unit_price']);
            $this->assign('receipt_total_amount', $roundedTotal);
            $this->assign('receipt_total_amount_formatted', number_format($roundedTotal, 0, ',', '.'));
            
            // Giá trị mới mặc định bằng giá trị hiện tại
            $this->assign('new_weight', round($receipt['weight']));
            $this->assign('new_unit_price', round($receipt['unit_price']));
            
            // Đánh dấu là đang tạo mới
            $this->assign('is_editing_existing', []);
            $this->assign('adjustment_id', 0);
        }
        
        return $this->render();
    }
    
    /**
     * Xử lý lưu đề nghị chỉnh sửa phiếu nhập hàng
     */
    public function postSaveAdjustment()
    {
        // Lấy dữ liệu từ request
        $receiptId = $this->request->getPost('receipt_id');
        $newWeight = round($this->request->getPost('new_weight'));
        $newUnitPrice = round($this->request->getPost('new_unit_price'));
        $oldWeight = round($this->request->getPost('old_weight'));
        $oldUnitPrice = round($this->request->getPost('old_unit_price'));
        $adjustmentId = $this->request->getPost('adjustment_id');
        
        // Kiểm tra dữ liệu đầu vào
        if (empty($receiptId) || empty($newWeight) || empty($newUnitPrice)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!'
            ]);
        }
        
        // Kiểm tra phiếu nhập tồn tại
        $receipt = $this->purchaseYardGoodsReceiptModel->find($receiptId);
        
        if (empty($receipt)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy phiếu nhập hàng!'
            ]);
        }
        
        // Kiểm tra giá trị mới có thay đổi so với cũ không
        if ($newWeight == $oldWeight && $newUnitPrice == $oldUnitPrice) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không có thay đổi nào so với dữ liệu hiện tại!'
            ]);
        }
        
        // Dữ liệu phiếu điều chỉnh
        $adjustmentData = [
            'receipt_id' => $receiptId,
            'old_weight' => $oldWeight,
            'old_unit_price' => $oldUnitPrice,
            'new_weight' => $newWeight,
            'new_unit_price' => $newUnitPrice,
            'status' => 0, // Pending
            'created_by' => $this->session->userId,
            'reviewed_by' => 0 // Chưa được duyệt
        ];
        
        // Xác định là tạo mới hay cập nhật phiếu điều chỉnh
        if (!empty($adjustmentId) && $adjustmentId > 0) {
            // Kiểm tra xem phiếu điều chỉnh có tồn tại không
            $existingAdjustment = $this->receiptAdjustmentModel->find($adjustmentId);
            if (!$existingAdjustment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không tìm thấy phiếu điều chỉnh để cập nhật!'
                ]);
            }
            
            // Kiểm tra xem phiếu đã được duyệt chưa
            if ($existingAdjustment['reviewed_by'] > 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Phiếu điều chỉnh đã được duyệt, không thể cập nhật!'
                ]);
            }
            
            // Cập nhật phiếu điều chỉnh hiện có
            $this->receiptAdjustmentModel->update($adjustmentId, [
                'new_weight' => $newWeight,
                'new_unit_price' => $newUnitPrice,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Phiếu điều chỉnh đã được cập nhật thành công!',
                'adjustment_id' => $adjustmentId
            ]);
        } else {
            // Tạo phiếu điều chỉnh mới
            $adjustmentId = $this->receiptAdjustmentModel->insertAdjustment($adjustmentData);
            
            if (empty($adjustmentId)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Lỗi khi lưu đề nghị chỉnh sửa!'
                ]);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Đề nghị chỉnh sửa đã được lưu thành công!',
                'adjustment_id' => $adjustmentId
            ]);
        }
    }
    
    /**
     * Hiển thị danh sách phiếu điều chỉnh
     */
    public function getAdjustment()
    {
        // Lấy user_id từ session
        $userId = $this->session->userId;
        
        // Lấy danh sách bãi được phân quyền cho user
        $authorizedYards = $this->userRoleAssignmentModel->getAuthorizedYardsByUserId($userId);
        
        if (empty($authorizedYards)) {
            $this->session->setFlashdata('error', 'Bạn không có quyền truy cập vào bất kỳ bãi nào.');
            $this->assign('adjustments', []);
            $this->assign('no_data_message', [['message' => 'Bạn không có quyền truy cập vào bất kỳ bãi nào.']]);
            $this->assign('error_message', $this->session->getFlashdata('error'));
            $this->assign('success_message', $this->session->getFlashdata('success'));
            return $this->render();
        }
        
        // Lấy danh sách yard_id
        $yardIds = array_column($authorizedYards, 'purchase_yard_id');
        
        // Lấy danh sách phiếu điều chỉnh chưa được duyệt (reviewed_by = 0) 
        // và thuộc các bãi được phân quyền
        $adjustments = $this->receiptAdjustmentModel->getPendingAdjustmentsByYardIds($yardIds);
        
        // Nếu không có phiếu điều chỉnh nào
        if (empty($adjustments)) {
            $this->assign('adjustments', []);
            $this->assign('no_data_message', [['message' => 'Không có phiếu điều chỉnh nào đang chờ duyệt.']]);
            $this->assign('error_message', $this->session->getFlashdata('error'));
            $this->assign('success_message', $this->session->getFlashdata('success'));
            return $this->render();
        }
        
        // Lấy thông tin về các dispatcher để kiểm tra quyền
        $dispatchers = $this->userRoleAssignmentModel->getUsersWithRole(\App\Constants\Roles::DISPATCHER);
        $dispatcherIds = array_column($dispatchers, 'user_id');
        
        // Format dữ liệu
        $formattedAdjustments = [];
        $yardList = []; // Danh sách các bãi để xây dựng dropdown filter
        
        foreach ($adjustments as $adjustment) {
            $createdBy = $adjustment['created_by'];
            $isCreator = ($createdBy == $userId);
            $creatorIsDispatcher = in_array($createdBy, $dispatcherIds);
            
            $actionButtons = '';
            
            // Nếu phiếu do người dùng hiện tại tạo, hiển thị nút "Huỷ"
            if ($isCreator) {
                $actionButtons = '<button class="btn btn-danger btn-cancel-adjustment" data-id="' . $adjustment['id'] . '">Huỷ phiếu</button>';
            } 
            // Nếu phiếu do người khác tạo và người đó có role DISPATCHER, hiển thị nút "Xác nhận"
            elseif ($creatorIsDispatcher) {
                $actionButtons = '<button class="btn btn-success btn-confirm-adjustment" data-id="' . $adjustment['id'] . '">Xác nhận</button>';
            }
            
            // Tính chênh lệch trọng lượng và số tiền
            $weightDifference = $adjustment['new_weight'] - $adjustment['old_weight'];
            $oldTotal = $adjustment['old_weight'] * $adjustment['old_unit_price'];
            $newTotal = $adjustment['new_weight'] * $adjustment['new_unit_price'];
            $valueDifference = $newTotal - $oldTotal;
            
            // Thu thập thông tin bãi cho dropdown filter
            $yardId = $adjustment['purchase_yard_id'];
            $yardName = $adjustment['yard_name'] . ' (' . $adjustment['yard_code'] . ')';
            if (!isset($yardList[$yardId])) {
                $yardList[$yardId] = [
                    'yard_id' => $yardId,
                    'yard_name' => $yardName
                ];
            }
            
            $formattedAdjustments[] = [
                'id' => $adjustment['id'],
                'yard_id' => $adjustment['purchase_yard_id'],
                'receipt_id' => $adjustment['receipt_id'],
                'yard_name' => $yardName,
                'vehicle_number' => $adjustment['vehicle_number'],
                'category_name' => $adjustment['category_name'],
                'old_weight' => $adjustment['old_weight'],
                'old_weight_formatted' => number_format($adjustment['old_weight'], 0, ',', '.'),
                'new_weight' => $adjustment['new_weight'],
                'new_weight_formatted' => number_format($adjustment['new_weight'], 0, ',', '.'),
                'weight_difference' => $weightDifference,
                'weight_difference_formatted' => number_format($weightDifference, 0, ',', '.'),
                'weight_difference_class' => $weightDifference < 0 ? 'text-danger' : 'text-success',
                'old_unit_price' => $adjustment['old_unit_price'],
                'old_unit_price_formatted' => number_format($adjustment['old_unit_price'], 0, ',', '.'),
                'new_unit_price' => $adjustment['new_unit_price'],
                'new_unit_price_formatted' => number_format($adjustment['new_unit_price'], 0, ',', '.'),
                'old_total' => $oldTotal,
                'old_total_formatted' => number_format($oldTotal, 0, ',', '.'),
                'new_total' => $newTotal,
                'new_total_formatted' => number_format($newTotal, 0, ',', '.'),
                'value_difference' => $valueDifference,
                'value_difference_formatted' => number_format($valueDifference, 0, ',', '.'),
                'value_difference_class' => $valueDifference < 0 ? 'text-danger' : 'text-success',
                'created_at' => date('d-m-Y H:i', strtotime($adjustment['created_at'])),
                'creator_name' => $adjustment['creator_name'] ?? 'N/A',
                'action_buttons' => $actionButtons
            ];
        }
        
        $this->assign('adjustments', $formattedAdjustments);
        $this->assign('yard_options', array_values($yardList));
        $this->assign('no_data_message', []);
        
        // Gán thông báo flash message từ session
        $this->assign('error_message', $this->session->getFlashdata('error'));
        $this->assign('success_message', $this->session->getFlashdata('success'));
        
        return $this->render();
    }
    
    /**
     * Xử lý xóa phiếu điều chỉnh
     */
    public function postCancelAdjustment()
    {
        $adjustmentId = $this->request->getPost('adjustment_id');
        
        if (empty($adjustmentId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy phiếu điều chỉnh!'
            ]);
        }
        
        // Lấy thông tin phiếu điều chỉnh
        $adjustment = $this->receiptAdjustmentModel->find($adjustmentId);
        
        if (empty($adjustment)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy phiếu điều chỉnh!'
            ]);
        }
        
        // Kiểm tra quyền hủy phiếu (chỉ người tạo mới được hủy)
        if ($adjustment['created_by'] != $this->session->userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Bạn không có quyền huỷ phiếu điều chỉnh này!'
            ]);
        }
        
        // Kiểm tra xem phiếu đã được duyệt chưa
        if ($adjustment['reviewed_by'] > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Phiếu điều chỉnh đã được duyệt, không thể huỷ!'
            ]);
        }
        
        // Xóa phiếu điều chỉnh
        $success = $this->receiptAdjustmentModel->delete($adjustmentId);
        
        if (!$success) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi huỷ phiếu điều chỉnh!'
            ]);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Phiếu điều chỉnh đã được huỷ thành công!'
        ]);
    }
    
    /**
     * Xử lý xác nhận phiếu điều chỉnh
     */
    public function postConfirmAdjustment()
    {
        $adjustmentId = $this->request->getPost('adjustment_id');
        
        if (empty($adjustmentId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy phiếu điều chỉnh!'
            ]);
        }
        
        // Lấy thông tin phiếu điều chỉnh
        $adjustment = $this->receiptAdjustmentModel->find($adjustmentId);
        
        if (empty($adjustment)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy phiếu điều chỉnh!'
            ]);
        }
        
        // Kiểm tra xem phiếu đã được duyệt chưa
        if ($adjustment['reviewed_by'] > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Phiếu điều chỉnh đã được duyệt trước đó!'
            ]);
        }
        
        // Lấy thông tin phiếu nhập hàng
        $receipt = $this->purchaseYardGoodsReceiptModel->find($adjustment['receipt_id']);
        
        if (empty($receipt)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy phiếu nhập hàng tương ứng!'
            ]);
        }
        
        // Bắt đầu transaction
        $this->purchaseYardGoodsReceiptModel->beginTransaction();
        
        try {
            // Cập nhật thông tin phiếu nhập hàng
            $this->purchaseYardGoodsReceiptModel->update($receipt['id'], [
                'weight' => $adjustment['new_weight'],
                'unit_price' => $adjustment['new_unit_price']
            ]);
            
            // Cập nhật trạng thái phiếu điều chỉnh
            $this->receiptAdjustmentModel->update($adjustment['id'], [
                'reviewed_by' => $this->session->userId,
                'status' => 1, // Đã duyệt
                'reviewed_at' => date('Y-m-d H:i:s')
            ]);
            
            // Cập nhật thông tin sản phẩm trong kho
            // Lấy thông tin sản phẩm hiện tại
            $productInfo = $this->purchaseYardProductInfoModel->getProductInfoByYardAndCategory(
                $receipt['purchase_yard_id'], 
                $receipt['category_id'],
                $receipt['currency_id'] ?? null
            );
            
            if (!empty($productInfo)) {
                // Tính chênh lệch trọng lượng
                $weightDifference = $adjustment['new_weight'] - $adjustment['old_weight'];
                
                // Tính lại trọng lượng và giá trung bình mới
                $newTotalWeight = $productInfo['stock_weight'] + $weightDifference;
                
                // Tính lại giá trung bình nếu có sự thay đổi đơn giá
                $oldValue = $adjustment['old_weight'] * $adjustment['old_unit_price'];
                $newValue = $adjustment['new_weight'] * $adjustment['new_unit_price'];
                $valueDifference = $newValue - $oldValue;
                
                // Tính tổng giá trị hiện tại
                $currentValue = $productInfo['stock_weight'] * $productInfo['average_price'];
                $newTotalValue = $currentValue + $valueDifference;
                
                // Tính giá trung bình mới
                $newAveragePrice = ($newTotalWeight > 0) ? ($newTotalValue / $newTotalWeight) : 0;
                
                // Cập nhật thông tin sản phẩm
                $this->purchaseYardProductInfoModel->update($productInfo['id'], [
                    'stock_weight' => $newTotalWeight,
                    'average_price' => $newAveragePrice
                ]);
            }
            
            // Commit transaction
            $this->purchaseYardGoodsReceiptModel->commitTransaction();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Phiếu điều chỉnh đã được xác nhận thành công!'
            ]);
            
        } catch (\Exception $e) {
            $this->purchaseYardGoodsReceiptModel->rollbackTransaction();
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
} 