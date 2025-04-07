<?php

namespace App\Controllers;

use App\Models\CashFundsModel;
use App\Models\CashFundCurrenciesModel;
use App\Models\CurrenciesModel;

class CashFund extends BaseController
{
    protected $cashFundsModel;
    protected $cashFundCurrenciesModel;
    protected $currenciesModel;
    
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, 
                                   \CodeIgniter\HTTP\ResponseInterface $response, 
                                   \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->cashFundsModel = new CashFundsModel();
        $this->cashFundCurrenciesModel = new CashFundCurrenciesModel();
        $this->currenciesModel = new CurrenciesModel();
    }
    
    /**
     * Implement abstract method isValidRole.
     */
    protected function isValidRole($role, $method, $segments)
    {
        return true;
    }
    
    /**
     * Hiển thị danh sách quỹ tiền.
     */
    public function getIndex()
    {
        $funds = $this->cashFundsModel->findAll();
        $this->assign('funds', $funds);
        return $this->render();
    }
    
    /**
     * Hiển thị form thêm mới quỹ tiền.
     * Tải danh sách các loại tiền tệ để hiển thị checkbox on/off.
     */
    public function getAdd()
    {
        $currencies = $this->currenciesModel->findAll();
        // Với form thêm mới, mặc định không có loại tiền nào được kích hoạt.
        foreach ($currencies as &$cur) {
            $cur['enabled'] = false;
        }
        $this->assign('currencies', $currencies);
        return $this->render();
    }
    
    /**
     * Xử lý thêm mới quỹ tiền.
     * Nếu người dùng chọn bật loại tiền (checkbox có tên "currency_{id}"), insert dòng mới với balance = 0.
     */
    public function postAdd()
    {
        $data = [
            'fund_code' => $this->request->getPost('fund_code'),
            'fund_name' => $this->request->getPost('fund_name'),
        ];
        $fundId = $this->cashFundsModel->insert($data);
        
        if ($fundId) {
            $currencies = $this->currenciesModel->findAll();
            foreach ($currencies as $currency) {
                // Nếu checkbox được chọn, field "currency_{id}" sẽ có giá trị (ví dụ: "on")
                if ($this->request->getPost('currency_' . $currency['id'])) {
                    $cfData = [
                        'cash_fund_id' => $fundId,
                        'currency_id'  => $currency['id'],
                        'balance'      => 0, // Balance mặc định là 0
                    ];
                    $this->cashFundCurrenciesModel->insert($cfData);
                }
            }
            $this->session->setFlashdata('success', 'Quỹ tiền đã được thêm mới.');
        } else {
            $this->session->setFlashdata('error', 'Thêm mới quỹ tiền thất bại.');
        }
        return redirect()->to("cash-fund");
    }
    
    /**
     * Hiển thị form chỉnh sửa quỹ tiền.
     * Tải thông tin quỹ và các loại tiền được sử dụng (dựa trên cash_fund_currencies).
     */
    public function getEdit($id)
    {
        $fund = $this->cashFundsModel->find($id);
        if (!$fund) {
            $this->session->setFlashdata('error', 'Quỹ tiền không tồn tại.');
            return redirect()->to("cash-fund");
        }
        // Lấy dữ liệu cash_fund_currencies của quỹ này thông qua model
        $cfCurrencies = $this->cashFundCurrenciesModel->getFundCurrencies($id);
        // Tạo mapping: currency_id => balance
        $balanceMapping = [];
        foreach ($cfCurrencies as $record) {
            $balanceMapping[$record['currency_id']] = $record['balance'];
        }
        $currencies = $this->currenciesModel->findAll();
        $currencyList = [];
        foreach ($currencies as $cur) {
            $cur['enabled'] = isset($balanceMapping[$cur['id']]);
            $cur['balance'] = isset($balanceMapping[$cur['id']]) ? $balanceMapping[$cur['id']] : '';
            $currencyList[] = $cur;
        }
        $this->assign('fund_id', $fund['id']);
        $this->assign('fund_code', $fund['fund_code']);
        $this->assign('fund_name', $fund['fund_name']);
        $this->assign('currencies', $currencyList);
        return $this->render();
    }
    
    /**
     * Xử lý cập nhật quỹ tiền.
     * Chỉ insert thêm các dòng cash_fund_currencies nếu checkbox được chọn.
     */
    public function postEdit($id)
    {
        $data = [
            'fund_code' => $this->request->getPost('fund_code'),
            'fund_name' => $this->request->getPost('fund_name'),
        ];
        
        if ($this->cashFundsModel->update($id, $data)) {
            // Xoá các dòng cũ
            $this->cashFundCurrenciesModel->where('cash_fund_id', $id)->delete();
            $currencies = $this->currenciesModel->findAll();
            foreach ($currencies as $currency) {
                if ($this->request->getPost('currency_' . $currency['id'])) {
                    $cfData = [
                        'cash_fund_id' => $id,
                        'currency_id'  => $currency['id'],
                        'balance'      => 0,
                    ];
                    $this->cashFundCurrenciesModel->insert($cfData);
                }
            }
            $this->session->setFlashdata('success', 'Quỹ tiền đã được cập nhật.');
        } else {
            $this->session->setFlashdata('error', 'Cập nhật quỹ tiền thất bại.');
        }
        return redirect()->to("cash-fund/edit/{$id}");
    }
    
    /**
     * Xóa quỹ tiền và các dòng liên quan trong cash_fund_currencies.
     */
    public function getDelete($id)
    {
        if ($this->cashFundsModel->delete($id)) {
            $this->cashFundCurrenciesModel->where('cash_fund_id', $id)->delete();
            $this->session->setFlashdata('success', 'Quỹ tiền đã được xóa.');
        } else {
            $this->session->setFlashdata('error', 'Xóa quỹ tiền thất bại.');
        }
        return redirect()->to("cash-fund");
    }
    
    /**
     * Hiển thị chi tiết thông tin quỹ tiền.
     */
    public function getDetail($id)
    {
        $fund = $this->cashFundsModel->find($id);
        if (!$fund) {
            $this->session->setFlashdata('error', 'Quỹ tiền không tồn tại.');
            return redirect()->to("cash-fund");
        }
        $cfCurrencies = $this->cashFundCurrenciesModel->getFundCurrencies($id);
        $this->assign('fund_id', $fund['id']);
        $this->assign('fund_code', $fund['fund_code']);
        $this->assign('fund_name', $fund['fund_name']);
        $this->assign('cfCurrencies', $cfCurrencies);
        return $this->render();
    }
}
