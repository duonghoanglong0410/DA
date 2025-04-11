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

class YardGoodsReceipt extends BaseController
{
    protected $canTuDongModel;
    protected $productCategoryModel;
    protected $purchaseYardModel;
    protected $purchaseYardGoodsReceiptModel;
    protected $purchaseYardProductInfoModel;
    protected $currencyModel;
    protected $userRoleAssignmentModel;
    
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
        
        // Lấy danh sách loại mặt hàng
        $products = $this->productCategoryModel->findAll();
        
        // Lấy danh sách loại tiền tệ từ purchase_yard_product_info
        $currencies = [];
        $yardProductInfos = $this->purchaseYardProductInfoModel->getInfoByYardIds($yardIds);
        
        // Nhóm theo bãi để xác định bãi nào có nhiều loại tiền tệ
        $yardCurrencies = [];
        foreach ($yardProductInfos as $info) {
            if (!isset($yardCurrencies[$info['purchase_yard_id']])) {
                $yardCurrencies[$info['purchase_yard_id']] = [];
            }
            $yardCurrencies[$info['purchase_yard_id']][] = $info['currency_id'];
        }
        
        // JSON encode yardCurrencies để sử dụng trong JavaScript
        $yardCurrenciesJson = json_encode($yardCurrencies);
        
        // Lấy thông tin tiền tệ
        $currencyIds = array_unique(array_column($yardProductInfos, 'currency_id'));
        if (!empty($currencyIds)) {
            $currencies = $this->currencyModel->whereIn('id', $currencyIds)->findAll();
        }
        
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
                'yard_id' => $yard_id,
                'input_date' => date('Y-m-d'),
                'vehicle_number' => $vehicle_number,
                'category_id' => $category_id,
                'quantity' => $quantity,
                'unit_price' => $unit_price,
                'created_by' => $this->session->userId
            ];
            
            $receipt_id = $this->purchaseYardGoodsReceiptModel->insert($receiptData);
            
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
                $selectedItemIds = explode(',', $selectedItems);
                $updateResults = [];
                
                foreach ($selectedItemIds as $itemId) {
                    // Skip if ID is 0 or empty
                    if (empty($itemId)) {
                        $updateResults['invalid_id_' . $itemId] = 'invalid id';
                        continue;
                    }
                    
                    // Verify if the item exists
                    $existingItem = $this->canTuDongModel->find($itemId);
                    
                    if ($existingItem) {
                        // Use direct DB query to avoid timestamp issues
                        $updateSql = "UPDATE can_tu_dong SET is_receipted = 1 WHERE id = ?";
                        $updateResult = $this->canTuDongModel->db->query($updateSql, [$itemId]);
                        $updateResults[$itemId] = $updateResult ? 'success' : 'failed';
                        
                        // Double-check update
                        $afterItem = $this->canTuDongModel->find($itemId);
                        $updateResults[$itemId . '_after'] = $afterItem['is_receipted'];
                    } else {
                        $updateResults[$itemId] = 'not found';
                    }
                }
                
                // Add log entry for debugging
                log_message('info', 'YardGoodsReceipt update results: ' . json_encode([
                    'selected_items' => $selectedItems,
                    'update_results' => $updateResults
                ]));
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
    
} 