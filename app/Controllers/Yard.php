<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PurchaseYardModel;
use App\Models\PurchaseYardCurrencyFundModel;
use App\Models\CurrencyModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Yard extends BaseController
{
    protected $purchaseYardModel;
    protected $purchaseYardCurrencyFundModel;
    protected $currencyModel;

    // Sử dụng initController của CodeIgniter 4 thay vì __construct()
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->purchaseYardModel             = new PurchaseYardModel();
        $this->purchaseYardCurrencyFundModel = new PurchaseYardCurrencyFundModel();
        $this->currencyModel                 = new CurrencyModel();
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
        return redirect()->to('yard/index');
    }

    // Hiển thị form sửa kho bãi và quản lý quỹ tiền (getEdit - view: getEdit)
    public function getEdit($yard_id)
    {
        $yard = $this->purchaseYardModel->find($yard_id);
        $cfCurrencies = $this->purchaseYardCurrencyFundModel->where('purchase_yard_id', $yard_id)->findAll();
        $currencies   = $this->currencyModel->findAll();

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
        $this->assign('cfCurrencies', $cfCurrencies);
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
        return redirect()->to('yard/index');
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
        return redirect()->to('yard/index');
    }
}
