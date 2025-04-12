<?php namespace App\Models;

use App\Models\BaseModel;
use CodeIgniter\I18n\Time;

class TripModel extends BaseModel
{
    protected $table      = 'trips';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'trip_code',
        'receipt_number',
        'vehicle_number',
        'avg_export_unit_price',
        'export_total_amount',
        'total_export_weight',
        'category_id',
        'purchase_yard_weighing_date',
        'factory_weighing_date',
        'factory_id',
        'purchase_yard_currency_fund_id',
        'document_number',
        'sold_weight',
        'net_weight',
        'selling_unit_price',
        'powder_percentage',
        'freight_fee',
        'customs_cost',
        'other_cost',
        'total_goods_amount',
        'impurity_percentage',
        'impurity_kg',
        'total_freight_amount',
        'remaining_goods_debt',
        'remaining_freight_debt',
        'remaining_customs_cost',
        'remaining_other_cost',
        'created_at',
        'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
        
    // Đếm số bản ghi trong trips có purchase_yard_currency_fund_id bằng $fundId
    public function countByCurrencyFundId($fundId)
    {
        return $this->where('purchase_yard_currency_fund_id', $fundId)->countAllResults();
    }
    
    /**
     * Lấy danh sách xe đang lưu thông (xe chưa giao hàng đến nhà máy)
     * Điều kiện: factory_id là null hoặc 0
     * 
     * @return array Danh sách xe đang lưu thông
     */
    public function getActiveExportTrips()
    {
        return $this->select('trips.*, product_categories.name as product_name')
            ->join('product_categories', 'product_categories.id = trips.category_id', 'left')
            ->where('trips.factory_id IS NULL OR trips.factory_id = 0')
            ->findAll();
    }
    
    /**
     * Lấy danh sách xe đang lưu thông với biển số xe cụ thể
     * 
     * @param string $vehicleNumber Biển số xe cần tìm
     * @return array Danh sách xe đang lưu thông với biển số xe cụ thể
     */
    public function getActiveExportTripsByVehicleNumber($vehicleNumber)
    {
        return $this->select('trips.*, product_categories.name as product_name')
            ->join('product_categories', 'product_categories.id = trips.category_id', 'left')
            ->where('trips.vehicle_number', $vehicleNumber)
            ->where('trips.factory_id IS NULL OR trips.factory_id = 0')
            ->findAll();
    }
    
    /**
     * Lấy danh sách xe đang lưu thông với loại mặt hàng cụ thể
     * 
     * @param int $categoryId ID loại mặt hàng
     * @return array Danh sách xe đang lưu thông của loại mặt hàng
     */
    public function getActiveExportTripsByCategory($categoryId)
    {
        return $this->select('trips.*, product_categories.name as product_name')
            ->join('product_categories', 'product_categories.id = trips.category_id', 'left')
            ->where('trips.category_id', $categoryId)
            ->where('trips.factory_id IS NULL OR trips.factory_id = 0')
            ->findAll();
    }
    
    /**
     * Tạo mã trip code theo định dạng mới
     * Format: TR + Ngày tháng năm + Số thứ tự trong ngày (3 chữ số)
     * 
     * @return string Mã trip code mới
     */
    protected function generateTripCode()
    {
        $today = date('Ymd');
        $prefix = 'TR' . $today;
        
        // Đếm số chuyến xe trong ngày
        $count = $this->where('trip_code LIKE', $prefix . '%')->countAllResults();
        
        // Tạo số thứ tự (3 chữ số)
        $sequenceNumber = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $sequenceNumber;
    }
    
    /**
     * Lấy currency fund ID dựa trên yardId và currencyId
     * 
     * @param int $yardId ID bãi
     * @param int $currencyId ID loại tiền tệ (mặc định null)
     * @return int|null ID của quỹ tiền tương ứng hoặc null nếu không tìm thấy
     */
    protected function getCurrencyFundId($yardId, $currencyId = null)
    {
        $purchaseYardCurrencyFundModel = new \App\Models\PurchaseYardCurrencyFundModel();
        $currencyFund = $purchaseYardCurrencyFundModel->where('purchase_yard_id', $yardId)
            ->where('currency_id', $currencyId ?: 1) // Mặc định lấy VND nếu không có currencyId
            ->first();
        
        return $currencyFund['id'] ?? null;
    }
    
    /**
     * Tạo dữ liệu cơ bản cho một chuyến xe
     * 
     * @param string $tripCode Mã chuyến xe
     * @param string $receiptNumber Số phiếu (null hoặc có giá trị)
     * @param string $vehicleNumber Biển số xe
     * @param int $categoryId ID loại mặt hàng
     * @param int $currencyFundId ID quỹ tiền tệ tại bãi
     * @param string|null $documentNumber Số chứng từ
     * @return array Mảng dữ liệu cơ bản của chuyến xe
     */
    protected function createBaseTripData($tripCode, $receiptNumber, $vehicleNumber, $categoryId, $currencyFundId)
    {
        return [
            'trip_code' => $tripCode,
            'receipt_number' => $receiptNumber,
            'vehicle_number' => $vehicleNumber,
            'avg_export_unit_price' => 0,
            'export_total_amount' => 0,
            'total_export_weight' => 0,
            'category_id' => $categoryId,
            'purchase_yard_weighing_date' => date('Y-m-d'),
            'factory_weighing_date' => null,
            'factory_id' => 0,
            'purchase_yard_currency_fund_id' => $currencyFundId,
            'document_number' => '',
            'sold_weight' => 0,
            'net_weight' => 0,
            'selling_unit_price' => 0,
            'powder_percentage' => 0,
            'freight_fee' => 0,
            'customs_cost' => 0,
            'other_cost' => 0,
            'total_goods_amount' => 0,
            'impurity_percentage' => 0,
            'impurity_kg' => 0,
            'total_freight_amount' => 0,
            'remaining_goods_debt' => 0,
            'remaining_freight_debt' => 0,
            'remaining_customs_cost' => 0,
            'remaining_other_cost' => 0
        ];
    }
    
    /**
     * Tạo chuyến xe mới (không bao gồm chi tiết)
     * 
     * @param string $vehicleNumber Biển số xe
     * @param int $categoryId ID loại mặt hàng
     * @param int $yardId ID bãi xuất hàng
     * @param int $currencyId ID loại tiền tệ (nếu có)
     * @return int|false ID của trip đã tạo hoặc false nếu thất bại
     */
    public function createTrip($vehicleNumber, $categoryId, $yardId, $currencyId = null)
    {
        // Ghi log thông tin đầu vào
        log_message('debug', 'TripModel::createTrip - Input - vehicleNumber: ' . $vehicleNumber . 
                           ', categoryId: ' . $categoryId . 
                           ', yardId: ' . $yardId . 
                           ', currencyId: ' . ($currencyId ?: 'null'));
        
        // Tạo mã trip code
        $tripCode = $this->generateTripCode();
        log_message('debug', 'TripModel::createTrip - Generated tripCode: ' . $tripCode);
        
        // Lấy currency fund id
        $currencyFundId = $this->getCurrencyFundId($yardId, $currencyId);
        log_message('debug', 'TripModel::createTrip - currencyFundId: ' . ($currencyFundId ?: 'null'));
        
        // Kiểm tra nếu không tìm thấy currency fund
        if (!$currencyFundId) {
            log_message('error', 'TripModel::createTrip - Không tìm thấy currency fund cho yardId: ' . 
                              $yardId . ', currencyId: ' . ($currencyId ?: 'null'));
            // Tạo currency fund nếu cần
            $purchaseYardCurrencyFundModel = new \App\Models\PurchaseYardCurrencyFundModel();
            $fundData = [
                'purchase_yard_id' => $yardId,
                'currency_id' => $currencyId ?: 1,
                'balance' => 0
            ];
            log_message('info', 'TripModel::createTrip - Tạo mới currency fund với data: ' . print_r($fundData, true));
            $currencyFundId = $purchaseYardCurrencyFundModel->insert($fundData);
            log_message('info', 'TripModel::createTrip - Kết quả tạo mới currency fund: ' . ($currencyFundId ?: 'false'));
        }
        
        // Tạo số phiếu
        $receiptNumber = 'XK' . date('Ymd');
        
        // Tạo dữ liệu cơ bản
        $data = $this->createBaseTripData($tripCode, $receiptNumber, $vehicleNumber, $categoryId, $currencyFundId);
        log_message('debug', 'TripModel::createTrip - Data: ' . print_r($data, true));
        
        // Thực hiện insert và ghi log kết quả
        $insertId = $this->insert($data);
        log_message('debug', 'TripModel::createTrip - Insert result: ' . ($insertId ?: 'false'));
        
        return $insertId;
    }
    
    /**
     * Thêm chi tiết cho chuyến xe
     * 
     * @param int $tripId ID chuyến xe
     * @param int $yardId ID bãi
     * @param int $userId ID người tạo
     * @param float $exportWeight Khối lượng xuất
     * @param float $exportUnitPrice Đơn giá xuất
     * @param int|null $warehouseId ID kho (nếu có)
     * @param int|null $buyerCurrencyFundId ID quỹ tiền tệ người mua (nếu có)
     * @return int|false ID của chi tiết đã tạo hoặc false nếu thất bại
     */
    public function addTripDetail($tripId, $yardId, $userId, $exportWeight, $exportUnitPrice, $warehouseId = null, $buyerCurrencyFundId = null)
    {
        $tripDetailModel = new \App\Models\TripDetailModel();
        
        $data = [
            'trip_id' => $tripId,
            'purchase_yard_id' => $yardId,
            'created_by' => $userId,
            'warehouse_id' => $warehouseId ?: 0,
            'buyer_currency_fund_id' => $buyerCurrencyFundId ?: 0,
            'export_weight' => $exportWeight,
            'export_unit_price' => $exportUnitPrice,
            'export_total_amount' => $exportWeight * $exportUnitPrice,
            'sold_weight' => 0,
            'net_weight' => 0,
            'selling_unit_price' => 0,
            'powder_percentage' => 0,
            'freight_fee' => 0,
            'customs_cost' => 0,
            'other_cost' => 0,
            'total_goods_amount' => 0,
            'impurity_percentage' => 0,
            'impurity_kg' => 0,
            'total_freight_amount' => 0
        ];
        
        $detailId = $tripDetailModel->insert($data);
        
        if ($detailId) {
            // Cập nhật thông tin tổng hợp trong bảng trips
            $this->updateTripAggregates($tripId);
        }
        
        return $detailId;
    }
    
    /**
     * Cập nhật thông tin tổng hợp của chuyến xe từ các chi tiết
     * 
     * @param int $tripId ID chuyến xe
     * @return boolean Kết quả cập nhật
     */
    public function updateTripAggregates($tripId)
    {
        $tripDetailModel = new \App\Models\TripDetailModel();
        
        // Lấy tất cả các chi tiết của chuyến xe
        $details = $tripDetailModel->where('trip_id', $tripId)->findAll();
        
        if (empty($details)) {
            return false;
        }
        
        // Tính toán các giá trị tổng hợp
        $totalExportWeight = 0;
        $totalExportAmount = 0;
        
        foreach ($details as $detail) {
            $totalExportWeight += $detail['export_weight'];
            $totalExportAmount += $detail['export_total_amount'];
        }
        
        // Tính giá bình quân
        $avgExportUnitPrice = ($totalExportWeight > 0) ? ($totalExportAmount / $totalExportWeight) : 0;
        
        // Cập nhật thông tin tổng hợp
        $updateData = [
            'total_export_weight' => $totalExportWeight,
            'export_total_amount' => $totalExportAmount,
            'avg_export_unit_price' => $avgExportUnitPrice,
        ];
        
        return $this->update($tripId, $updateData);
    }
    
    /**
     * Lập phiếu xuất hàng trực tiếp (tạo trip và trip_detail)
     */
    public function create($yardId, $categoryId, $deliveryType, $vehicleNumber, $quantity, $unitPrice, $userId, $currencyId = null)
    {
        // Tạo chuyến xe
        $tripId = $this->createTrip($vehicleNumber, $categoryId, $yardId, $currencyId);
        
        if (!$tripId) {
            return false;
        }
        
        // Thêm chi tiết chuyến xe
        $detailId = $this->addTripDetail($tripId, $yardId, $userId, $quantity, $unitPrice);
        
        return ($detailId) ? $tripId : false;
    }
    
    /**
     * Lập phiếu xuất hàng từ phiếu cân
     */
    public function createFromScaleReceipts($yardId, $categoryId, $deliveryType, $vehicleNumber, $quantity, $unitPrice, $scaleIds, $userId, $currencyId = null)
    {
        // Tạo chuyến xe
        $tripId = $this->createTrip($vehicleNumber, $categoryId, $yardId, $currencyId);
        
        if (!$tripId) {
            return false;
        }
        
        // Thêm chi tiết chuyến xe
        $detailId = $this->addTripDetail($tripId, $yardId, $userId, $quantity, $unitPrice);
        
        return ($detailId) ? $tripId : false;
    }
    
    /**
     * Lập phiếu xuất hàng từ xe tải
     */
    public function createFromTruck($yardId, $categoryId, $deliveryType, $vehicleNumber, $quantity, $unitPrice, $truckId, $userId, $currencyId = null)
    {
        // Kiểm tra xe tải đã tồn tại
        $existingTrip = $this->find($truckId);
        
        if (!$existingTrip) {
            return false;
        }
        
        // Thêm chi tiết cho chuyến xe đã tồn tại
        $detailId = $this->addTripDetail($truckId, $yardId, $userId, $quantity, $unitPrice);
        
        if ($detailId) {
            // Nếu thêm chi tiết thành công, trả về ID của chuyến xe đã tồn tại
            return $truckId;
        }
        
        return false;
    }
    
    /**
     * Thêm phiếu cân bổ sung vào chuyến xe đã có
     * 
     * @param int $tripId ID chuyến xe
     * @param int $yardId ID bãi
     * @param float $quantity Khối lượng
     * @param float $unitPrice Đơn giá
     * @param int $userId ID người tạo
     * @return boolean Kết quả thêm
     */
    public function addAdditionalScale($tripId, $yardId, $quantity, $unitPrice, $userId)
    {
        // Thêm chi tiết chuyến xe
        $detailId = $this->addTripDetail($tripId, $yardId, $userId, $quantity, $unitPrice);
        
        return (bool) $detailId;
    }
}
