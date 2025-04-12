<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CanTuDongModel;
use App\Models\ProductCategoryModel;
use App\Models\PurchaseYardModel;
use App\Models\PurchaseYardProductInfoModel;
use App\Models\CurrencyModel;
use App\Models\UserRoleAssignmentModel;
use App\Models\TripModel;
use CodeIgniter\I18n\Time;

class YardGoodsDelivery extends BaseController
{
    protected $canTuDongModel;
    protected $productCategoryModel;
    protected $purchaseYardModel;
    protected $purchaseYardProductInfoModel;
    protected $currencyModel;
    protected $userRoleAssignmentModel;
    protected $tripModel;
    
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Khởi tạo các model
        $this->canTuDongModel = new CanTuDongModel();
        $this->productCategoryModel = new ProductCategoryModel();
        $this->purchaseYardModel = new PurchaseYardModel();
        $this->purchaseYardProductInfoModel = new PurchaseYardProductInfoModel();
        $this->currencyModel = new CurrencyModel();
        $this->userRoleAssignmentModel = new UserRoleAssignmentModel();
        $this->tripModel = new TripModel();
    }
    
    // Triển khai phương thức isValidRole theo quy tắc
    public function isValidRole($role, $method, $segments)
    {
        // TODO: Implement role validation
        return true;
    }
    
    /**
     * Hiển thị trang lập phiếu cân xuất hàng
     */
    public function getIndex()
    {
        $currentDate = date('Y-m-d');
        $formattedDate = date('d-m-Y');

        $userId = $this->session->userId;
        
        // Lấy danh sách bãi được phân quyền cho user
        $authorizedYards = $this->userRoleAssignmentModel->getAuthorizedYardsByUserId($userId);
        
        if (empty($authorizedYards)) {
            $this->session->setFlashdata('error', 'Bạn không có quyền truy cập vào bất kỳ bãi nào.');
            $this->assign('xk_can_data', []);
            $this->assign('no_data_message', []);
            $this->assign('products', []);
            $this->assign('currencies', []);
            $this->assign('authorized_yards', []);
            $this->assign('trucks', []);
            return $this->render();
        }
        
        // Lấy danh sách yard_id
        $yardIds = array_column($authorizedYards, 'purchase_yard_id');
        
        // Lấy danh sách phiếu cân XK trong ngày
        $weighingData = $this->canTuDongModel->getTodayReceiptDataByYardIds($currentDate, $yardIds, 'XK');
        
        // Format dữ liệu phiếu cân
        $xkCanData = [];
        $noDataMessage = [];
        
        if (!empty($weighingData)) {
            foreach ($weighingData as $index => $data) {
                // Skip if already processed
                if ($data['is_receipted'] == 1) {
                    continue;
                }
                
                $data['row_id'] = 'scale-row-' . $data['id'];
                $data['KLhang_formatted'] = number_format($data['KLhang'], 0, ',', '.');
                $data['Dongia_formatted'] = $data['Dongia'] ? number_format($data['Dongia'], 0, ',', '.') : '';
                $xkCanData[] = $data;
            }
        }
        
        if (empty($xkCanData)) {
            $noDataMessage[] = [
                'message' => 'Không có phiếu cân XK nào trong ngày hôm nay.'
            ];
        }
        
        // Lấy danh sách loại mặt hàng
        $products = $this->purchaseYardProductInfoModel->getProductCategoriesByYardIds($yardIds);
        
        // Kiểm tra nếu chỉ có một loại mặt hàng
        $singleProductCategory = count($products) === 1;
        $autoSelectedCategoryId = '';
        $autoSelectedCategoryName = '';
        
        if ($singleProductCategory) {
            $autoSelectedCategoryId = $products[0]['id'];
            $autoSelectedCategoryName = $products[0]['name'];
        }
        
        // Lấy danh sách xe đang lưu thông hiện tại
        $trucks = $this->tripModel->getActiveExportTrips();
        
        // Nhóm các xe tải theo category_id
        $trucksByCategory = [];
        $noTrucksMessage = [];
        
        if (!empty($trucks)) {
            // Format dữ liệu xe tải
            foreach ($trucks as &$truck) {
                $truck['total_export_weight_formatted'] = number_format($truck['total_export_weight'], 0, ',', '.');
                $truck['product_category_id'] = $truck['category_id']; // Đảm bảo có trường product_category_id để dùng trong JavaScript
                
                // Nhóm theo category_id
                if (!isset($trucksByCategory[$truck['category_id']])) {
                    $trucksByCategory[$truck['category_id']] = [];
                }
                $trucksByCategory[$truck['category_id']][] = $truck;
            }
        } else {
            $noTrucksMessage = [['message' => 'Không có xe tải nào đang vận chuyển.']];
        }
        
        // Lưu trữ danh sách currency của yard
        $yardCurrencies = [];
        $allYardCurrencies = [];
        
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
        
        // Import Constants để lấy giá trị SINGLE_SCALE_SELECTION
        $singleScaleSelection = \App\Constants\Constants::SINGLE_SCALE_SELECTION;
        
        // Gán dữ liệu cho view
        $this->assign('current_date', $formattedDate);
        $this->assign('xk_can_data', $xkCanData);
        $this->assign('no_data_message', $noDataMessage);
        $this->assign('products', $products);
        $this->assign('authorized_yards', $authorizedYards);
        $this->assign('currencies', $currencies);
        $this->assign('trucks', $trucks);
        $this->assign('trucks_by_category_json', json_encode($trucksByCategory));
        $this->assign('single_product_category', $singleProductCategory ? 'true' : 'false');
        $this->assign('auto_selected_category_id', $autoSelectedCategoryId);
        $this->assign('auto_selected_category_name', $autoSelectedCategoryName);
        $this->assign('yard_currencies_json', json_encode($yardCurrencies));
        $this->assign('no_trucks_message', $noTrucksMessage);
        $this->assign('single_scale_selection', $singleScaleSelection ? 'true' : 'false');
        
        return $this->render();
    }
    
    /**
     * Kiểm tra xe tồn tại và trả về thông tin lựa chọn
     */
    public function postCheckVehicle()
    {
        $vehicleNumber = $this->request->getPost('vehicle_number');
        
        if (empty($vehicleNumber)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vui lòng nhập biển số xe.'
            ]);
        }
        
        // Tìm các xe đang lưu thông với biển số tương ứng
        $existingTrips = $this->tripModel->getActiveExportTripsByVehicleNumber($vehicleNumber);
        
        if (empty($existingTrips)) {
            return $this->response->setJSON([
                'success' => true,
                'exists' => false,
                'message' => 'Không tìm thấy xe đang vận chuyển với biển số này.'
            ]);
        }
        
        // Format dữ liệu cho frontend
        $options = [];
        foreach ($existingTrips as $trip) {
            $options[] = [
                'id' => $trip['id'],
                'trip_code' => $trip['trip_code'],
                'vehicle_number' => $trip['vehicle_number'],
                'total_export_weight' => $trip['total_export_weight'],
                'total_export_weight_formatted' => number_format($trip['total_export_weight'], 0, ',', '.'),
                'product_name' => $trip['product_name'] ?? 'Không xác định'
            ];
        }
        
        return $this->response->setJSON([
            'success' => true,
            'exists' => true,
            'trips' => $options,
            'message' => 'Tìm thấy xe đang vận chuyển với biển số này.'
        ]);
    }

    /**
     * Xử lý lưu phiếu cân xuất hàng
     */
    public function postSave()
    {
        $data = $this->request->getPost();
        
        // Validate dữ liệu
        if (empty($data['yard_id']) || empty($data['delivery_type']) || 
            empty($data['category_id']) || empty($data['vehicle_number']) || 
            empty($data['quantity']) || empty($data['unit_price'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ thông tin.'
            ]);
        }
        
        // Kiểm tra nếu cần tiền tệ
        $yardCurrencies = $this->currencyModel->getCurrenciesByYardId($data['yard_id']);
        if (count($yardCurrencies) > 1 && empty($data['currency_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vui lòng chọn loại tiền tệ.'
            ]);
        }
        
        // Bắt đầu transaction
        $this->tripModel->beginTransaction();
        
        try {
            // Xử lý theo phương thức đã chọn
            $method = $data['selected_method'];
            $tripId = false;
            $currencyId = !empty($data['currency_id']) ? $data['currency_id'] : null;
            
            // Ghi log thông tin trước khi tạo trip
            log_message('debug', 'YardGoodsDelivery: Bắt đầu tạo trip - Phương thức: ' . $method . 
                       ', Bãi ID: ' . $data['yard_id'] . 
                       ', Loại hàng ID: ' . $data['category_id'] . 
                       ', Biển số: ' . $data['vehicle_number'] . 
                       ', Khối lượng: ' . $data['quantity'] . 
                       ', Đơn giá: ' . $data['unit_price'] . 
                       ', Tiền tệ ID: ' . ($currencyId ?? 'null'));
            
            switch ($method) {
                case 'scale':
                    // Lập phiếu từ phiếu cân
                    $selectedItems = !empty($data['selected_items']) ? explode(',', $data['selected_items']) : [];
                    if (empty($selectedItems)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Không có phiếu cân nào được chọn.'
                        ]);
                    }
                    
                    // Kiểm tra nếu người dùng đã chọn xe tải hiện có
                    if (!empty($data['existing_trip_id'])) {
                        // Sử dụng xe tải đã có, thêm chi tiết vào
                        $tripId = $this->tripModel->createFromTruck(
                            $data['yard_id'],
                            $data['category_id'],
                            $data['delivery_type'],
                            $data['vehicle_number'],
                            $data['quantity'],
                            $data['unit_price'],
                            $data['existing_trip_id'],
                            $this->session->userId,
                            $currencyId
                        );
                    } else {
                        // Tạo bản ghi trip mới từ phiếu cân
                        $tripId = $this->tripModel->createFromScaleReceipts(
                            $data['yard_id'],
                            $data['category_id'],
                            $data['delivery_type'],
                            $data['vehicle_number'],
                            $data['quantity'],
                            $data['unit_price'],
                            $selectedItems,
                            $this->session->userId,
                            $currencyId
                        );
                    }
                    
                    // Đánh dấu phiếu cân đã được sử dụng
                    if ($tripId) {
                        $this->canTuDongModel->markAsReceipted($selectedItems);
                    }
                    break;
                    
                case 'truck':
                    // Lập phiếu từ xe tải đang vận chuyển
                    $selectedTruck = $data['selected_truck'];
                    if (empty($selectedTruck)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Không có xe tải nào được chọn.'
                        ]);
                    }
                    
                    // Thêm chi tiết vào chuyến xe đã tồn tại
                    $tripId = $this->tripModel->createFromTruck(
                        $data['yard_id'],
                        $data['category_id'],
                        $data['delivery_type'],
                        $data['vehicle_number'],
                        $data['quantity'],
                        $data['unit_price'],
                        $selectedTruck,
                        $this->session->userId,
                        $currencyId
                    );
                    break;
                    
                case 'new':
                case 'direct':
                    // Tạo bản ghi trip trực tiếp
                    $tripId = $this->tripModel->create(
                        $data['yard_id'],
                        $data['category_id'],
                        $data['delivery_type'],
                        $data['vehicle_number'],
                        $data['quantity'],
                        $data['unit_price'],
                        $this->session->userId,
                        $currencyId
                    );
                    break;
                    
                default:
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Phương thức không hợp lệ.'
                    ]);
            }
            
            if (!$tripId) {
                // Nếu không thể tạo trip, rollback
                $this->tripModel->rollbackTransaction();
                
                // Ghi log lỗi chi tiết
                $errors = null;
                if (method_exists($this->tripModel, 'errors')) {
                    $errors = $this->tripModel->errors();
                    log_message('error', 'Lỗi khi tạo chuyến xe: ' . print_r($errors, true));
                }
                
                // Kiểm tra và ghi log currency fund
                $purchaseYardCurrencyFundModel = new \App\Models\PurchaseYardCurrencyFundModel();
                $fund = $purchaseYardCurrencyFundModel->where('purchase_yard_id', $data['yard_id'])
                    ->where('currency_id', $currencyId ?: 1)
                    ->first();
                
                log_message('error', 'Currency Fund cho Yard ID ' . $data['yard_id'] . 
                           ' và Currency ID ' . ($currencyId ?: 1) . ': ' . 
                           ($fund ? 'Tìm thấy (ID: ' . $fund['id'] . ')' : 'Không tìm thấy'));
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo chuyến xe.' . 
                                ($errors ? ' Chi tiết: ' . json_encode($errors) : '')
                ]);
            }
            
            // Lấy thông tin chuyến xe vừa tạo
            $tripInfo = $this->tripModel->find($tripId);
            
            // Cập nhật giảm tồn kho trong purchase_yard_product_info
            $productInfo = $this->purchaseYardProductInfoModel->getProductInfoByYardAndCategory(
                $data['yard_id'],
                $data['category_id'],
                $currencyId
            );
            
            if ($productInfo) {
                // Tính toán số lượng tồn kho mới
                $oldStockWeight = $productInfo['stock_weight'];
                $newStockWeight = $oldStockWeight - $data['quantity'];
                
                if ($newStockWeight < 0) {
                    // Ghi log cảnh báo nếu xuất vượt tồn kho
                    log_message('warning', 'Xuất hàng vượt tồn kho: Bãi ID=' . $data['yard_id'] . 
                                ', Loại hàng ID=' . $data['category_id'] . 
                                ', Tồn kho=' . $oldStockWeight . 
                                ', Xuất=' . $data['quantity']);
                    $newStockWeight = 0; // Đảm bảo tồn kho không âm
                }
                
                // Cập nhật tồn kho mới
                $updateResult = $this->purchaseYardProductInfoModel->update($productInfo['id'], [
                    'stock_weight' => $newStockWeight
                ]);
                
                if (!$updateResult) {
                    // Ghi log lỗi nếu cập nhật thất bại
                    log_message('error', 'Không thể cập nhật tồn kho: ' . 
                                $this->purchaseYardProductInfoModel->errors());
                } else {
                    // Ghi log thành công
                    log_message('info', 'Đã cập nhật tồn kho: Bãi ID=' . $data['yard_id'] . 
                               ', Loại hàng ID=' . $data['category_id'] . 
                               ', Tồn kho cũ=' . $oldStockWeight . 
                               ', Xuất=' . $data['quantity'] . 
                               ', Tồn kho mới=' . $newStockWeight);
                    

                }
            } else {
                $this->tripModel->rollbackTransaction();
            
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . 'Không tìm thấy thông tin sản phẩm trong kho: Bãi ID=' . $data['yard_id'] . 
                          ', Loại hàng ID=' . $data['category_id']
                ]);    
            }
            
            // Commit nếu mọi thứ thành công
            $this->tripModel->commitTransaction();
            
            // Xác định loại thông báo dựa trên phương thức
            $successMessage = 'Tạo chuyến xe thành công. Mã chuyến: ' . $tripInfo['trip_code'];
            if ($method === 'truck' || !empty($data['existing_trip_id'])) {
                $successMessage = 'Cập nhật chuyến xe thành công. Mã chuyến: ' . $tripInfo['trip_code'];
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => $successMessage
            ]);
            
        } catch (\Exception $e) {
            // Rollback nếu có lỗi
            $this->tripModel->rollbackTransaction();
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Lấy thông tin sản phẩm trong kho
     */
    public function postProductInfo()
    {
        $yardId = $this->request->getPost('yard_id');
        $categoryId = $this->request->getPost('category_id');
        $currencyId = $this->request->getPost('currency_id');
        
        if (empty($yardId) || empty($categoryId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Thiếu thông tin cần thiết.'
            ]);
        }
        
        // Tìm thông tin sản phẩm trong kho
        $productInfo = $this->purchaseYardProductInfoModel->getProductInfoByYardAndCategory(
            $yardId, 
            $categoryId, 
            $currencyId
        );
        
        if (!$productInfo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy thông tin sản phẩm.',
                'product_info' => [
                    'stock_weight' => 0,
                    'average_price' => 0
                ]
            ]);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'product_info' => [
                'stock_weight' => $productInfo['stock_weight'],
                'stock_weight_formatted' => number_format($productInfo['stock_weight'], 0, ',', '.'),
                'average_price' => $productInfo['average_price'],
                'average_price_formatted' => number_format($productInfo['average_price'], 0, ',', '.')
            ]
        ]);
    }
} 