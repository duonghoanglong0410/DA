<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CanTuDongModel;
use App\Models\ProductCategoryModel;
use App\Models\PurchaseYardModel;
use App\Models\PurchaseYardProductInfoModel;
use App\Models\CurrencyModel;
use App\Models\DeliveryAdjustmentModel;
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
    protected $deliveryAdjustmentModel;
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
        $this->deliveryAdjustmentModel = new DeliveryAdjustmentModel();
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
        return $this->render();
    }
    /**
     * Hiển thị trang lập phiếu cân xuất hàng
     */
    public function getCreate()
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

    /**
     * Hiển thị danh sách phiếu xuất kho
     */
    public function getHistory()
    {
        // Lấy user_id từ session
        $userId = $this->session->userId;
        
        // Lấy danh sách bãi được phân quyền cho user
        $authorizedYards = $this->userRoleAssignmentModel->getAuthorizedYardsByUserId($userId);
        
        if (empty($authorizedYards)) {
            $this->session->setFlashdata('error', 'Bạn không có quyền truy cập vào bất kỳ bãi nào.');
            $this->assign('no_data_message', [['message' => 'Bạn không có quyền truy cập vào bất kỳ bãi nào.']]);
            $this->assign('has_deliveries', []);
            $this->assign('today_date', date('Y-m-d'));
            $this->assign('history_days', 30);
            return $this->render();
        }
        
        // Lấy danh sách yard_id
        $yardIds = array_column($authorizedYards, 'purchase_yard_id');
        
        // Lấy số ngày lịch sử từ Constants
        $historyDays = \App\Constants\Constants::RECEIPT_HISTORY_DAYS;
        
        // Tính ngày bắt đầu (N ngày trước)
        $startDate = date('Y-m-d', strtotime('-' . $historyDays . ' days'));
        $endDate = date('Y-m-d');
        
        // Lấy danh sách phiếu xuất kho từ N ngày trước đến hiện tại
        // Chỉ lấy những phiếu chưa thu tiền khách mua (remaining_goods_debt = total_goods_amount)
        $deliveryDetails = $this->tripModel->getDeliveryDetailsWithinDateRange($startDate, $endDate, $yardIds);
        
        // Xác định có nhiều bãi hay không
        $hasMultipleYards = count($authorizedYards) > 1;
        
        // Chuẩn bị danh sách bãi cho dropdown filter
        $yardOptions = [];
        foreach ($authorizedYards as $yard) {
            $yardOptions[] = [
                'yard_id' => $yard['purchase_yard_id'],
                'yard_name' => $yard['yard_name'] . ' (' . $yard['yard_code'] . ')'
            ];
        }
        
        // Format dữ liệu
        $formattedDeliveries = [];
        foreach ($deliveryDetails as $index => $delivery) {
            $formattedDeliveries[] = [
                'id' => $delivery['id'],
                'trip_id' => $delivery['trip_id'],
                'trip_code' => $delivery['trip_code'],
                'yard_id' => $delivery['purchase_yard_id'],
                'yard_name' => $delivery['yard_name'] . ' (' . $delivery['yard_code'] . ')',
                'vehicle_number' => $delivery['vehicle_number'],
                'category_name' => $delivery['category_name'],
                'delivery_date' => date('d-m-Y', strtotime($delivery['created_at'])),
                'weight' => $delivery['weight'],
                'weight_formatted' => number_format($delivery['weight'], 0, ',', '.'),
                'unit_price' => $delivery['unit_price'],
                'unit_price_formatted' => number_format($delivery['unit_price'], 0, ',', '.'),
                'total_amount' => $delivery['weight'] * $delivery['unit_price'],
                'total_amount_formatted' => number_format($delivery['weight'] * $delivery['unit_price'], 0, ',', '.'),
                'currency_symbol' => $delivery['currency_symbol'] ?? 'VNĐ',
                'creator_name' => $delivery['creator_name'] ?? 'N/A',
                'row_num' => $index + 1
            ];
        }
        
        // Set up view data 
        if (empty($formattedDeliveries)) {
            $this->assign('no_data_message', [['message' => 'Không có phiếu xuất kho nào chưa thu tiền trong ' . $historyDays . ' ngày qua.']]);
            $this->assign('deliveries', []);
            $this->assign('delivery_content_class', 'd-none');
        } else {
            $this->assign('no_data_message', []);
            $this->assign('deliveries', $formattedDeliveries);
            $this->assign('delivery_content_class', '');
        }
        
        // Gán CSS display class dựa trên số lượng bãi
        $this->assign('yards_display_class', $hasMultipleYards ? '' : 'd-none');
        
        // Gán yard options cho filter (luôn gán để tránh lỗi trong view)
        $this->assign('yard_options', $yardOptions);
        
        // Gán dữ liệu ra view
        $this->assign('today_date', date('Y-m-d'));
        $this->assign('history_days', $historyDays);
        
        return $this->render();
    }

    /**
     * Hiển thị form tạo phiếu đề nghị chỉnh sửa xuất kho
     */
    public function getCreateAdjustment($id = null)
    {
        if (empty($id)) {
            $this->session->setFlashdata('error', 'Không tìm thấy phiếu xuất kho cần chỉnh sửa.');
            return redirect()->to('yard-goods-delivery/history');
        }
        
        // Lấy thông tin chi tiết xuất kho
        $tripDetailModel = new \App\Models\TripDetailModel();
        $deliveryDetail = $tripDetailModel->getDeliveryDetailById($id);
        
        if (!$deliveryDetail) {
            $this->session->setFlashdata('error', 'Không tìm thấy phiếu xuất kho cần chỉnh sửa.');
            return redirect()->to('yard-goods-delivery/history');
        }
        
        // Lấy thông tin trip
        $trip = $this->tripModel->find($deliveryDetail['trip_id']);
        
        if (!$trip) {
            $this->session->setFlashdata('error', 'Không tìm thấy thông tin chuyến xe.');
            return redirect()->to('yard-goods-delivery/history');
        }
        
        // Format dữ liệu và gán từng thuộc tính riêng lẻ
        $totalAmount = $deliveryDetail['export_weight'] * $deliveryDetail['export_unit_price'];
        $currencySymbol = $deliveryDetail['currency_symbol'] ?? 'VNĐ';
        
        // Gán từng thuộc tính riêng lẻ ra view
        $this->assign('delivery_id', $deliveryDetail['id']);
        $this->assign('trip_id', $deliveryDetail['trip_id']);
        $this->assign('trip_code', $trip['trip_code']);
        $this->assign('yard_id', $deliveryDetail['purchase_yard_id']);
        $this->assign('yard_name', $deliveryDetail['yard_name'] . ' (' . $deliveryDetail['yard_code'] . ')');
        $this->assign('vehicle_number', $trip['vehicle_number']);
        $this->assign('category_name', $deliveryDetail['category_name']);
        $this->assign('delivery_date', date('d-m-Y', strtotime($deliveryDetail['created_at'])));
        $this->assign('old_weight', round($deliveryDetail['export_weight']));
        $this->assign('old_weight_formatted', number_format(round($deliveryDetail['export_weight']), 0, ',', '.'));
        $this->assign('old_unit_price', round($deliveryDetail['export_unit_price']));
        $this->assign('old_unit_price_formatted', number_format($deliveryDetail['export_unit_price'], 0, ',', '.'));
        $this->assign('total_amount', $totalAmount);
        $this->assign('total_amount_formatted', number_format($totalAmount, 0, ',', '.'));
        $this->assign('currency_symbol', $currencySymbol);
        $this->assign('creator_name', $deliveryDetail['creator_name'] ?? 'N/A');
        
        $this->assign('today_date', date('Y-m-d'));
        
        return $this->render();
    }
    
    /**
     * Xử lý lưu phiếu đề nghị chỉnh sửa xuất kho
     */
    public function postCreateAdjustment()
    {
        $data = $this->request->getPost();
        
        // Validate dữ liệu
        if (empty($data['delivery_id']) || empty($data['new_weight']) || empty($data['new_unit_price'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ thông tin.'
            ]);
        }
        
        try {
            // Bắt đầu transaction
            $this->tripModel->beginTransaction();
            
            // Lấy thông tin chi tiết xuất kho
            $tripDetailModel = new \App\Models\TripDetailModel();
            $deliveryDetail = $tripDetailModel->find($data['delivery_id']);
            
            if (!$deliveryDetail) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không tìm thấy phiếu xuất kho cần chỉnh sửa.'
                ]);
            }
            
            // Kiểm tra phiếu điều chỉnh chưa duyệt
            $deliveryAdjustmentModel = new \App\Models\DeliveryAdjustmentModel();
            $existingAdjustment = $deliveryAdjustmentModel->where('delivery_id', $data['delivery_id'])
                ->where('reviewed_by', 0) // Chưa duyệt
                ->first();
            
            if ($existingAdjustment) {
                // Cập nhật phiếu điều chỉnh cũ
                $existingAdjustmentData = [
                    'old_weight' => $deliveryDetail['export_weight'], // Lấy từ thông tin chi tiết
                    'old_unit_price' => $deliveryDetail['export_unit_price'], // Lấy từ thông tin chi tiết
                    'new_weight' => $data['new_weight'],
                    'new_unit_price' => $data['new_unit_price'],
                    'created_by' => $this->session->userId, // Cập nhật người tạo
                ];
                
                $deliveryAdjustmentModel->update($existingAdjustment['id'], $existingAdjustmentData);
            } else {
                // Nếu không có phiếu điều chỉnh chưa duyệt, tạo mới
                $adjustment = [
                    'delivery_id' => $data['delivery_id'],
                    'old_weight' => $deliveryDetail['export_weight'], // Lấy từ thông tin chi tiết
                    'old_unit_price' => $deliveryDetail['export_unit_price'], // Lấy từ thông tin chi tiết
                    'new_weight' => $data['new_weight'],
                    'new_unit_price' => $data['new_unit_price'],
                    'status' => 0, // Chờ duyệt
                    'created_by' => $this->session->userId,
                ];
                
                $adjustmentId = $deliveryAdjustmentModel->insert($adjustment);
                
                if (!$adjustmentId) {
                    $this->tripModel->rollbackTransaction();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Có lỗi xảy ra khi tạo phiếu chỉnh sửa.'
                    ]);
                }
            }
            
            // Commit transaction
            $this->tripModel->commitTransaction();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Đã tạo hoặc cập nhật phiếu đề nghị chỉnh sửa thành công. Vui lòng chờ duyệt.'
            ]);
            
        } catch (\Exception $e) {
            $this->tripModel->rollbackTransaction();
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Hiển thị danh sách phiếu điều chỉnh xuất kho
     * 
     * Phương thức này lấy danh sách các phiếu điều chỉnh xuất kho mà người dùng hiện tại
     * đã tạo hoặc có quyền truy cập, sau đó phân trang và định dạng dữ liệu
     * theo chuẩn hiển thị của Việt Nam.
     */
    public function getAdjustment()
    {
        // Lấy user_id từ session
        $userId = $this->session->userId;
        
        // Kiểm tra quyền truy cập
        $authorizedYards = $this->userRoleAssignmentModel->getAuthorizedYardsByUserId($userId);
        
        if (empty($authorizedYards)) {
            return redirect()->to('yard-goods-delivery')->with('error', 'Bạn không có quyền truy cập vào bất kỳ bãi nào.');
        }
        
        // Lấy danh sách yard_id được phân quyền
        $yardIds = array_column($authorizedYards, 'purchase_yard_id');
        
        // Lấy danh sách phiếu điều chỉnh của bãi được phân quyền
        $adjustments = $this->deliveryAdjustmentModel->getAdjustmentsByYardIds($yardIds);

        if (empty($adjustments)) {
            return redirect()->to('yard-goods-delivery')->with('error', 'Không có phiếu điều chỉnh nào.');
        }
        
        // Lấy thông tin về các dispatcher để kiểm tra quyền
        $dispatchers = $this->userRoleAssignmentModel->getUsersWithRole(\App\Constants\Roles::DISPATCHER);
        $dispatcherIds = array_column($dispatchers, 'user_id');
        
        // Chuẩn bị dữ liệu cho view
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
                'delivery_id' => $adjustment['delivery_id'],
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
        $this->assign('no_data_display', 'none');
        
        return $this->render();
    }

    public function postCancelAdjustment()
    {
        // Lấy thông tin từ POST request
        $adjustmentId = $this->request->getPost('adjustment_id');
        $userId = $this->session->userId;
        
        // Kiểm tra xem có phiếu điều chỉnh không
        if (empty($adjustmentId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy ID phiếu điều chỉnh.'
            ]);
        }
        
        // Bắt đầu transaction
        $this->tripModel->beginTransaction();
        
        try {
            // Lấy thông tin phiếu điều chỉnh
            $adjustment = $this->deliveryAdjustmentModel->find($adjustmentId);
            
            if (!$adjustment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Không tìm thấy phiếu điều chỉnh.'
                ]);
            }
            
            // Kiểm tra quyền - chỉ người tạo mới được huỷ
            if ($adjustment['created_by'] != $userId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Bạn không có quyền huỷ phiếu điều chỉnh này.'
                ]);
            }
            
            // Kiểm tra trạng thái - chỉ huỷ phiếu chờ duyệt
            if ($adjustment['status'] != 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Chỉ có thể huỷ phiếu điều chỉnh đang chờ duyệt.'
                ]);
            }
            
            // Thực hiện xoá phiếu điều chỉnh
            $deleted = $this->deliveryAdjustmentModel->delete($adjustmentId);
            
            if (!$deleted) {
                $this->tripModel->rollbackTransaction();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi huỷ phiếu điều chỉnh.'
                ]);
            }
            
            // Commit transaction
            $this->tripModel->commitTransaction();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Đã huỷ phiếu điều chỉnh thành công.'
            ]);
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->tripModel->rollbackTransaction();
            
            log_message('error', 'Lỗi khi huỷ phiếu điều chỉnh: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Xử lý xác nhận phiếu điều chỉnh
     * Xử lý xác nhận phiếu điều chỉnh xuất kho
     * 
     * Phương thức này cho phép người dùng có quyền Dispatcher xác nhận
     * và áp dụng thay đổi từ phiếu điều chỉnh xuất kho vào dữ liệu thực tế.
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function postConfirmAdjustment()
    {
        // Lấy thông tin từ POST request
        $adjustmentId = $this->request->getPost('adjustment_id');
        $userId = $this->session->userId;
        
        // Kiểm tra xem có phiếu điều chỉnh không
        if (empty($adjustmentId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy ID phiếu điều chỉnh.'
            ]);
        }
        
        // Lấy thông tin phiếu điều chỉnh
        $adjustment = $this->deliveryAdjustmentModel->find($adjustmentId);
        
        if (!$adjustment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy phiếu điều chỉnh.'
            ]);
        }
        // Kiểm tra trạng thái - chỉ xác nhận phiếu chờ duyệt
        if ($adjustment['status'] != 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Chỉ có thể xác nhận phiếu điều chỉnh đang chờ duyệt.'
            ]);
        }
        
        // Lấy chi tiết xuất kho để kiểm tra bãi
        $tripDetailModel = new \App\Models\TripDetailModel();
        $deliveryDetail = $tripDetailModel->find($adjustment['delivery_id']);
        
        if (!$deliveryDetail) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy phiếu xuất kho tương ứng.'
            ]);
        }
        
        // Lấy danh sách bãi được phân quyền cho user hiện tại
        $authorizedYards = $this->userRoleAssignmentModel->getAuthorizedYardsByUserId($userId);
        $authorizedYardIds = array_column($authorizedYards, 'purchase_yard_id');
        
        // Kiểm tra quyền xác nhận dựa trên bãi
        if (!in_array($deliveryDetail['purchase_yard_id'], $authorizedYardIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Bạn không có quyền xác nhận phiếu điều chỉnh cho bãi này.'
            ]);
        }
        
        // Bắt đầu transaction
        $this->tripModel->beginTransaction();
        try {
            
            // Thông tin chi tiết xuất kho đã được lấy để kiểm tra quyền truy cập
            // Sử dụng lại biến $deliveryDetail
            
            // Cập nhật thông tin trong trip_details
            $oldWeight = $deliveryDetail['export_weight'];
            $oldUnitPrice = $deliveryDetail['export_unit_price'];
            $newWeight = $adjustment['new_weight'];
            $newUnitPrice = $adjustment['new_unit_price'];
            
            // Tính lại thành tiền xuất
            $newTotalAmount = $newWeight * $newUnitPrice;
            
            // Cập nhật chi tiết xuất kho
            $updateData = [
                'export_weight' => $newWeight,
                'export_unit_price' => $newUnitPrice,
                'export_total_amount' => $newTotalAmount
            ];
            
            $updated = $tripDetailModel->update($adjustment['delivery_id'], $updateData);
            
            if (!$updated) {
                $this->tripModel->rollbackTransaction();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật phiếu xuất kho.'
                ]);
            }
            
            // Cập nhật tổng trọng lượng của trip
            $tripId = $deliveryDetail['trip_id'];
            $trip = $this->tripModel->find($tripId);
            
            if ($trip) {
                // Tính lại tổng trọng lượng và tổng tiền xuất
                $weightDiff = $newWeight - $oldWeight;
                $newTotalWeight = $trip['total_export_weight'] + $weightDiff;
                $newTotalExportAmount = $trip['export_total_amount'] - ($oldWeight * $oldUnitPrice) + $newTotalAmount;
                
                // Cập nhật trip
                $this->tripModel->update($tripId, [
                    'total_export_weight' => $newTotalWeight,
                    'export_total_amount' => $newTotalExportAmount,
                    'avg_export_unit_price' => ($newTotalWeight > 0) ? ($newTotalExportAmount / $newTotalWeight) : 0
                ]);
            }
            
            // Cập nhật thông tin tồn kho (nếu cần)
            $weightDiff = $newWeight - $oldWeight;
            
            if ($weightDiff != 0) {
                // Nếu khối lượng có thay đổi, cập nhật tồn kho
                $purchaseYardProductInfoModel = new \App\Models\PurchaseYardProductInfoModel();
                $productInfo = $purchaseYardProductInfoModel->getProductInfoByYardAndCategory(
                    $deliveryDetail['purchase_yard_id'],
                    $trip['category_id'],
                    $deliveryDetail['purchase_yard_currency_fund_id']
                );
                
                if ($productInfo) {
                    $newStockWeight = $productInfo['stock_weight'] - $weightDiff;
                    
                    if ($newStockWeight < 0) {
                        $newStockWeight = 0; // Đảm bảo tồn kho không âm
                        log_message('warning', 'Điều chỉnh xuất hàng vượt tồn kho: Bãi ID=' . $deliveryDetail['purchase_yard_id'] . 
                                    ', Loại hàng ID=' . $trip['category_id'] . 
                                    ', Tồn kho=' . $productInfo['stock_weight'] . 
                                    ', Chênh lệch=' . $weightDiff);
                    }
                    
                    $purchaseYardProductInfoModel->update($productInfo['id'], [
                        'stock_weight' => $newStockWeight
                    ]);
                }
            }
            
            // Cập nhật trạng thái phiếu điều chỉnh
            $this->deliveryAdjustmentModel->update($adjustmentId, [
                'status' => 1, // Đã duyệt
                'approved_by' => $userId
            ]);
            
            // Commit transaction
            $this->tripModel->commitTransaction();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Đã xác nhận và áp dụng phiếu điều chỉnh thành công.'
            ]);
            
        } catch (\Exception $e) {
            // Rollback transaction
            $this->tripModel->rollbackTransaction();
            
            log_message('error', 'Lỗi khi xác nhận phiếu điều chỉnh: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
}