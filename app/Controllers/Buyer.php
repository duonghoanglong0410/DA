<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BuyerModel;
use App\Models\BuyerCurrencyFundModel;
use App\Models\CurrencyModel;
use App\Models\TripModel;
use App\Models\CashVoucherModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Buyer extends BaseController
{
    protected $buyerModel;
    protected $buyerCurrencyFundModel;
    protected $currencyModel;
    protected $tripModel;
    protected $cashVoucherModel;

    // Sử dụng initController của CodeIgniter 4 thay vì __construct()
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->buyerModel             = new BuyerModel();
        $this->buyerCurrencyFundModel = new BuyerCurrencyFundModel();
        $this->currencyModel          = new CurrencyModel();
        $this->tripModel              = new TripModel();
        $this->cashVoucherModel       = new CashVoucherModel();
    }

    // Mỗi controller phải implement hàm isValidRole, trả về true mặc định
    public function isValidRole($role, $method, $segments)
    {
        return true;
    }

    // GET: Hiển thị danh sách nhà máy (view: getIndex.php)
    public function getIndex()
    {
        $buyers = $this->buyerModel->findAll();
        $this->assign('buyers', $buyers);
        return $this->render();
    }

    // GET: Hiển thị form thêm nhà máy (view: getAdd.php)
    public function getAdd()
    {
        $this->assign('currencies', $this->currencyModel->findAll());
        $this->assign('name', '');
        $this->assign('address', '');
        return $this->render();
    }

    // POST: Xử lý thêm nhà máy và các quỹ tiền liên quan
    public function postAdd()
    {
        $post = $this->request->getPost();

        // Ràng buộc: Bắt buộc phải chọn ít nhất một loại tiền tệ
        if (!isset($post['currency_active']) || count($post['currency_active']) == 0) {
            return redirect()->to('buyer/add')->with('error', 'Vui lòng chọn ít nhất một loại tiền tệ.');
        }

        $data = [
            'name'       => $post['name'],
            'address'    => $post['address'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->buyerModel->insert($data);
        $buyer_id = $this->buyerModel->insertID();

        if (isset($post['currency_active']) && is_array($post['currency_active'])) {
            foreach ($post['currency_active'] as $currency_id => $active) {
                if ($active == 1) {
                    $fundData = [
                        'buyer_id'       => $buyer_id,
                        'currency_id'    => $currency_id,
                        'remaining_debt' => 0, // Số dư nợ mặc định là 0
                        'created_at'     => date('Y-m-d H:i:s'),
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ];
                    $this->buyerCurrencyFundModel->insert($fundData);
                }
            }
        }
        return redirect()->to('buyer');
    }

    // GET: Hiển thị form sửa nhà máy (view: getEdit.php)
    public function getEdit($buyer_id)
    {
        $buyer = $this->buyerModel->find($buyer_id);
        $cfFunds = $this->buyerCurrencyFundModel->where('buyer_id', $buyer_id)->findAll();
        $currencies = $this->currencyModel->findAll();

        // Xử lý mảng currencies: nếu loại tiền đã được thiết lập cho nhà máy thì gán active
        foreach ($currencies as &$currency) {
            $found = false;
            foreach ($cfFunds as $cf) {
                if ($cf['currency_id'] == $currency['id']) {
                    $found = true;
                    break;
                }
            }
            $currency['active'] = $found ? 'checked' : '';
        }
        unset($currency);

        $this->assign('buyer_id', $buyer['id']);
        $this->assign('name', $buyer['name']);
        $this->assign('address', $buyer['address']);
        $this->assign('currencies', $currencies);
        return $this->render();
    }

    // POST: Xử lý cập nhật thông tin nhà máy và các quỹ tiền liên quan
    public function postEdit($buyer_id)
    {
        $post = $this->request->getPost();

        // Ràng buộc: Phải chọn ít nhất một loại tiền tệ
        if (!isset($post['currency_active']) || count($post['currency_active']) == 0) {
            return redirect()->to('buyer/edit/' . $buyer_id)->with('error', 'Vui lòng chọn ít nhất một loại tiền tệ.');
        }

        $data = [
            'name'       => $post['name'],
            'address'    => $post['address'],
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->buyerModel->update($buyer_id, $data);

        // Lấy danh sách quỹ tiền hiện có của nhà máy
        $existingFunds = $this->buyerCurrencyFundModel->where('buyer_id', $buyer_id)->findAll();
        $submittedCurrencyIds = isset($post['currency_active']) ? array_keys($post['currency_active']) : [];

        // Xóa các quỹ tiền không có trong dữ liệu submit
        foreach ($existingFunds as $fund) {
            if (!in_array($fund['currency_id'], $submittedCurrencyIds)) {
                // Kiểm tra: Số dư nợ còn lại phải = 0
                if ($fund['remaining_debt'] != 0) {
                    $currency = $this->currencyModel->find($fund['currency_id']);
                    return redirect()->to('buyer')->with('error', 'Không thể xoá quỹ tiền cho loại tiền "' . $currency['name'] . '" vì Số dư nợ còn lại khác 0.');
                }
                // Kiểm tra tham chiếu qua model (sử dụng hàm isReferenced của BuyerCurrencyFundModel)
                if ($this->buyerCurrencyFundModel->isReferenced($fund['id'])) {
                    $currency = $this->currencyModel->find($fund['currency_id']);
                    return redirect()->to('buyer')->with('error', 'Không thể xoá quỹ tiền cho loại tiền "' . $currency['name'] . '" vì đã được sử dụng trong chuyến xe hoặc phiếu thu tiền.');
                }
                $this->buyerCurrencyFundModel->delete($fund['id']);
            }
        }

        // Xử lý thêm mới các quỹ tiền được chọn (nếu chưa tồn tại)
        if (isset($post['currency_active'])) {
            foreach ($post['currency_active'] as $currency_id => $active) {
                if ($active == 1) {
                    $existingFund = $this->buyerCurrencyFundModel->where([
                        'buyer_id'    => $buyer_id,
                        'currency_id' => $currency_id
                    ])->first();
                    if (!$existingFund) {
                        $insertData = [
                            'buyer_id'       => $buyer_id,
                            'currency_id'    => $currency_id,
                            'remaining_debt' => 0,
                        ];
                        $this->buyerCurrencyFundModel->insert($insertData);
                    }
                }
            }
        }
        return redirect()->to('buyer');
    }

    // GET: Xử lý xoá nhà máy và các quỹ tiền liên quan
    public function getDelete($buyer_id)
    {
        $funds = $this->buyerCurrencyFundModel->where('buyer_id', $buyer_id)->findAll();
        foreach ($funds as $fund) {
            if ($fund['remaining_debt'] != 0) {
                $currency = $this->currencyModel->find($fund['currency_id']);
                return redirect()->to('buyer')->with('error', 'Không thể xoá nhà máy vì quỹ tiền cho loại tiền "' . $currency['name'] . '" có Số dư nợ còn lại khác 0.');
            }
            if ($this->buyerCurrencyFundModel->isReferenced($fund['id'])) {
                $currency = $this->currencyModel->find($fund['currency_id']);
                return redirect()->to('buyer')->with('error', 'Không thể xoá nhà máy vì quỹ tiền cho loại tiền "' . $currency['name'] . '" đã được sử dụng trong chuyến xe hoặc phiếu thu tiền.');
            }
        }
        foreach ($funds as $fund) {
            $this->buyerCurrencyFundModel->delete($fund['id']);
        }
        $this->buyerModel->delete($buyer_id);
        return redirect()->to('buyer');
    }
}
