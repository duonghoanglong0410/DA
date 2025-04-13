<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PurchaseYardModel;
use App\Models\PurchaseYardCurrencyFundModel;
use App\Models\CurrencyModel;
use App\Models\PurchaseYardProductInfoModel;
use App\Models\UserRoleAssignmentModel;
use App\Models\ProductCategoryModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Yard extends BaseController
{
    protected $purchaseYardModel;
    protected $purchaseYardCurrencyFundModel;
    protected $currencyModel;
    protected $userRoleAssignmentModel;
    protected $purchaseYardProductInfoModel;
    protected $productCategoryModel;

    // Sử dụng initController của CodeIgniter 4 thay vì __construct()
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->purchaseYardModel             = new PurchaseYardModel();
        $this->purchaseYardCurrencyFundModel = new PurchaseYardCurrencyFundModel();
        $this->currencyModel                 = new CurrencyModel();
        $this->userRoleAssignmentModel       = new UserRoleAssignmentModel();
        $this->purchaseYardProductInfoModel  = new PurchaseYardProductInfoModel();
        $this->productCategoryModel          = new ProductCategoryModel();
    }

    // Mỗi controller phải implement hàm isValidRole, trả về true mặc định
    public function isValidRole($role, $method, $segments)
    {
        return true;
    }

    // Hiển thị danh sách kho bãi (getIndex - view: getIndex)
    public function getIndex()
    {
        $yards = $this->purchaseYardModel->findAll();
        // Chuyển đổi status thành status_text cho từng kho bãi
        foreach ($yards as &$yard) {
            $yard['status_text'] = ($yard['status'] == 1) ? 'Hoạt động' : 'Ngừng';
        }
        $this->assign('yards', $yards);
        return $this->render();
    }
    

    // Hiển thị form thêm kho bãi và quản lý quỹ tiền (getAdd - view: getAdd)
    public function getAdd()
    {
        $this->assign('currencies', $this->currencyModel->findAll());
        $this->assign('yard_code', '');
        $this->assign('yard_name', '');
        $this->assign('client_api_key', '');
        $this->assign('status_active', 'selected');
        $this->assign('status_inactive', '');
        return $this->render();
    }

    // Xử lý thêm kho bãi và quỹ tiền (postAdd)
    public function postAdd()
    {
        $post = $this->request->getPost();

        $data = [
            'yard_code'      => $post['yard_code'],
            'yard_name'      => $post['yard_name'],
            'client_api_key' => isset($post['client_api_key']) ? $post['client_api_key'] : '',
            'status'         => isset($post['status']) ? $post['status'] : 1,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];
        $this->purchaseYardModel->insert($data);
        $yard_id = $this->purchaseYardModel->insertID();

        if (isset($post['currency_active']) && is_array($post['currency_active'])) {
            foreach ($post['currency_active'] as $currency_id => $active) {
                if ($active == 1) {
                    $fundData = [
                        'purchase_yard_id' => $yard_id,
                        'currency_id'      => $currency_id,
                        'balance'          => 0, // Số dư mặc định là 0
                        'created_at'       => date('Y-m-d H:i:s'),
                        'updated_at'       => date('Y-m-d H:i:s'),
                    ];
                    $this->purchaseYardCurrencyFundModel->insert($fundData);
                }
            }
        }
        return redirect()->to('yard');
    }

    // Hiển thị form sửa kho bãi và quản lý quỹ tiền (getEdit - view: getEdit)
    public function getEdit($yard_id)
    {
        $yard = $this->purchaseYardModel->find($yard_id);
        $cfCurrencies = $this->purchaseYardCurrencyFundModel->where('purchase_yard_id', $yard_id)->findAll();
        $currencies = $this->currencyModel->findAll();
    
        // Xử lý mảng currencies để gán active và balance_input nếu loại tiền đã được chọn
        foreach ($currencies as &$currency) {
            $found = false;
            $balance = 0;
            foreach ($cfCurrencies as $cf) {
                if ($cf['currency_id'] == $currency['id']) {
                    $found = true;
                    $balance = $cf['balance'];
                    break;
                }
            }
            if ($found) {
                $currency['active'] = 'checked';
            } else {
                $currency['active'] = '';
            }
        }
        unset($currency);
    
        $this->assign('yard_id', $yard['id']);
        $this->assign('yard_code', $yard['yard_code']);
        $this->assign('yard_name', $yard['yard_name']);
        $this->assign('client_api_key', $yard['client_api_key']);
        if ($yard['status'] == 1) {
            $this->assign('status_active', 'selected');
            $this->assign('status_inactive', '');
        } else {
            $this->assign('status_active', '');
            $this->assign('status_inactive', 'selected');
        }
        $this->assign('currencies', $currencies);
        return $this->render();
    }
    

    // Xử lý cập nhật thông tin kho bãi và quỹ tiền (postEdit)
    public function postEdit($yard_id)
    {
        $post = $this->request->getPost();

        $data = [
            'yard_code'      => $post['yard_code'],
            'yard_name'      => $post['yard_name'],
            'client_api_key' => isset($post['client_api_key']) ? $post['client_api_key'] : '',
            'status'         => isset($post['status']) ? $post['status'] : 1,
            'updated_at'     => date('Y-m-d H:i:s'),
        ];
        $this->purchaseYardModel->update($yard_id, $data);

        // Lấy danh sách quỹ tiền hiện có của kho bãi
        $existingFunds = $this->purchaseYardCurrencyFundModel->where('purchase_yard_id', $yard_id)->findAll();
        $submittedCurrencyIds = isset($post['currency_active']) ? array_keys($post['currency_active']) : [];

        // Xóa các quỹ tiền không có trong dữ liệu submit
        foreach ($existingFunds as $fund) {
            if (!in_array($fund['currency_id'], $submittedCurrencyIds)) {
                // Kiểm tra xem quỹ tiền có được tham chiếu hay không
                if ($this->purchaseYardCurrencyFundModel->isReferenced($fund['id'])) {
                    return redirect()->back()->with('error', 'Không thể xóa quỹ tiền cho loại tiền ID ' . $fund['currency_id'] . ' vì đã được tham chiếu.');
                }
                // Kiểm tra số dư bằng 0
                if ($fund['balance'] != 0) {
                    return redirect()->back()->with('error', 'Không thể xóa quỹ tiền cho loại tiền ID ' . $fund['currency_id'] . ' vì số dư khác 0.');
                }
                $this->purchaseYardCurrencyFundModel->delete($fund['id']);
            }
        }

        // Xử lý thêm mới các quỹ tiền được chọn (nếu chưa tồn tại)
        if (isset($post['currency_active'])) {
            foreach ($post['currency_active'] as $currency_id => $active) {
                if ($active == 1) {
                    $existingFund = $this->purchaseYardCurrencyFundModel->where([
                        'purchase_yard_id' => $yard_id,
                        'currency_id'      => $currency_id
                    ])->first();
                    if (!$existingFund) {
                        $insertData = [
                            'purchase_yard_id' => $yard_id,
                            'currency_id'      => $currency_id,
                            'balance'          => 0,
                            'created_at'       => date('Y-m-d H:i:s'),
                            'updated_at'       => date('Y-m-d H:i:s'),
                        ];
                        $this->purchaseYardCurrencyFundModel->insert($insertData);
                    }
                }
            }
        }
        return redirect()->to('yard');
    }

    // Xử lý xóa kho bãi và các quỹ tiền liên quan (getDelete)
    public function getDelete($yard_id)
    {
        $funds = $this->purchaseYardCurrencyFundModel->where('purchase_yard_id', $yard_id)->findAll();
        foreach ($funds as $fund) {
            if ($this->purchaseYardCurrencyFundModel->isReferenced($fund['id'])) {
                return redirect()->back()->with('error', 'Không thể xóa kho bãi vì quỹ tiền cho loại tiền ID ' . $fund['currency_id'] . ' đã được tham chiếu.');
            }
            if ($fund['balance'] != 0) {
                return redirect()->back()->with('error', 'Không thể xóa kho bãi vì quỹ tiền cho loại tiền ID ' . $fund['currency_id'] . ' có số dư khác 0.');
            }
        }
        foreach ($funds as $fund) {
            $this->purchaseYardCurrencyFundModel->delete($fund['id']);
        }
        $this->purchaseYardModel->delete($yard_id);
        return redirect()->to('yard');
    }

    /**
     * Hiển thị form điều chỉnh tồn kho và giá bình quân
     */
    public function getStock($id)
    {
        // Kiểm tra quyền truy cập
        $userId = $this->session->userId;
        $authorizedYards = $this->userRoleAssignmentModel->getAuthorizedYardsByUserId($userId);
        
        if (!$authorizedYards) {
            return redirect()->to('yard/dashboard')->with('error', 'Bạn không có quyền truy cập vào kho bãi này.');
        }

        // Lấy thông tin tồn kho
        $productInfo = $this->purchaseYardProductInfoModel->find($id);
        if (!$productInfo) {
            return redirect()->to('yard/dashboard')->with('error', 'Không tìm thấy thông tin tồn kho.');
        }

        // Kiểm tra quyền truy cập kho bãi
        $yardIds = array_column($authorizedYards, 'purchase_yard_id');
        if (!in_array($productInfo['purchase_yard_id'], $yardIds)) {
            return redirect()->to('yard/dashboard')->with('error', 'Bạn không có quyền truy cập vào kho bãi này.');
        }

        // Lấy thông tin bãi và loại sản phẩm
        $yard = $this->purchaseYardModel->find($productInfo['purchase_yard_id']);
        $category = $this->productCategoryModel->find($productInfo['category_id']);
        $currency = $this->currencyModel->find($productInfo['currency_id']);

        // Gán dữ liệu cho view
        $this->assign('id', $id);
        $this->assign('yard_name', $yard['yard_name']);
        $this->assign('yard_code', $yard['yard_code']);
        $this->assign('category_name', $category['name']);
        $this->assign('currency_symbol', $currency['symbol']);
        $this->assign('stock_weight', $productInfo['stock_weight']);
        $this->assign('stock_weight_formatted', number_format($productInfo['stock_weight'], 0, ',', '.'));
        $this->assign('average_price', $productInfo['average_price']);
        $this->assign('average_price_formatted', number_format($productInfo['average_price'], 0, ',', '.'));

        return $this->render();
    }

    /**
     * Xử lý cập nhật tồn kho và giá bình quân
     */
    public function postStock($id)
    {
        // Kiểm tra quyền truy cập
        $userId = $this->session->userId;
        $authorizedYards = $this->userRoleAssignmentModel->getAuthorizedYardsByUserId($userId);
        
        if (!$authorizedYards) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập vào kho bãi này.'
            ]);
        }

        // Lấy thông tin tồn kho
        $productInfo = $this->purchaseYardProductInfoModel->find($id);
        if (!$productInfo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Không tìm thấy thông tin tồn kho.'
            ]);
        }

        // Kiểm tra quyền truy cập kho bãi
        $yardIds = array_column($authorizedYards, 'purchase_yard_id');
        if (!in_array($productInfo['purchase_yard_id'], $yardIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập vào kho bãi này.'
            ]);
        }

        // Lấy dữ liệu từ form
        $newStockWeight = $this->request->getPost('stock_weight');
        $newAveragePrice = $this->request->getPost('average_price');

        // Validate dữ liệu
        if (!is_numeric($newStockWeight) || !is_numeric($newAveragePrice)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.'
            ]);
        }

        // Cập nhật thông tin
        $updateData = [
            'stock_weight' => $newStockWeight,
            'average_price' => $newAveragePrice,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!$this->purchaseYardProductInfoModel->update($id, $updateData)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật thông tin.'
            ]);
        }

        // Create stock adjustment record
        $stockAdjustmentModel = new \App\Models\StockAdjustmentModel();
        $adjustmentData = [
            'warehouse_id' => 0, // Not from warehouse
            'purchase_yard_product_info_id' => $id,
            'created_by' => $userId,
            'old_stock' => $productInfo['stock_weight'],
            'new_stock' => $newStockWeight,
            'old_average_price' => $productInfo['average_price'],
            'new_average_price' => $newAveragePrice,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $stockAdjustmentModel->insert($adjustmentData);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Đã cập nhật thông tin thành công.'
        ]);
    }
    
    /**
     * Hiển thị tổng quan kho bãi
     */
    public function getDashboard()
    {
        $allYardInfo = $this->userRoleAssignmentModel->getAuthorizedYardsByUserId($this->session->userId);

        if (empty($allYardInfo)) {
            return redirect()->to('/')->with('error', 'Bạn không có quyền truy cập vào bất kỳ kho bãi nào.');
        }

        $allYardByUserId = array_column($allYardInfo, 'purchase_yard_id');

        $yardCurrencies = $this->purchaseYardCurrencyFundModel->getFundCurrencies($allYardByUserId);

        $yardProducts = $this->purchaseYardProductInfoModel->getInfoByYardIds($allYardByUserId);

        $this->assign('allYardInfo', $allYardInfo);
        $this->assign('yardCurrencies', $yardCurrencies);
        $this->assign('yardProducts', $yardProducts);

        return $this->render();
    }
}
