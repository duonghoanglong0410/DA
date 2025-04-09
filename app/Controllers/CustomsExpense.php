<?php

namespace App\Controllers;

use App\Models\CashJournalModel;
use App\Models\DebtSettlementModel;
use App\Models\PurposeModel;
use App\Controllers\BaseController;

class CustomsExpense extends BaseController
{
    protected $cashJournalModel;
    protected $debtSettlementModel;
    protected $purposeModel;

    // Sử dụng initController thay vì __construct
    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger);
        // Khai báo các model cần thiết
        $this->cashJournalModel   = new CashJournalModel();
        $this->debtSettlementModel = new DebtSettlementModel();
        $this->purposeModel        = new PurposeModel();
    }

    // Hàm kiểm tra quyền (mặc định trả về true)
    public function isValidRole($role, $method, $segments)
    {
        return true;
    }

    /* ---------------------------- 9.1 Lập phiếu theo dõi ---------------------------- */

    // GET: Hiển thị form lập phiếu theo dõi
    public function getAddFollowup()
    {
        return $this->render(); // View: getAddFollowup.php
    }

    // POST: Xử lý lưu phiếu theo dõi vào custom_purposes
    public function postAddFollowup()
    {
        $data = [
            'voucher_date'     => $this->request->getPost('voucher_date'),
            'purpose_name'     => $this->request->getPost('purpose_name'),
            'amount'           => $this->request->getPost('amount'),
            'description'      => $this->request->getPost('description'),
            'created_by'       => $this->session->userId,
        ];
        $this->purposeModel->insert($data);
        return redirect()->to('customs-expense/list-followup');
    }

    /* ---------------------------- 9.2 Lập phiếu thu tiền ---------------------------- */

    // GET: Hiển thị form lập phiếu thu tiền
    public function getAddCashReceipt()
    {
        return $this->render(); // View: getAddCashReceipt.php
    }

    // POST: Xử lý lưu phiếu thu tiền khi form được submit
    public function postAddCashReceipt()
    {
        $data = [
            'created_date'     => $this->request->getPost('created_date'),
            'amount'           => $this->request->getPost('amount'),
            'description'      => $this->request->getPost('description'),
            'transaction_type' => 'thu',
            'is_followup'      => 0,
            'created_by'       => $this->session->userId,
        ];
        $this->cashJournalModel->insert($data);
        return redirect()->to('customs-expense/list-cash-journal');
    }

    /* ---------------------------- 9.3 Thanh toán công nợ ---------------------------- */

    // 9.3.1 - GET: Hiển thị danh sách phiếu theo dõi chưa thanh toán
    public function getPaymentSelection()
    {
        $followups = $this->cashJournalModel->getUnsettledFollowups();
        $this->assign('followups', $followups);
        return $this->render(); // View: getPaymentSelection.php
    }

    // 9.3.2 - POST: Lập phiếu thanh toán công nợ theo phiếu theo dõi (sau khi chọn các phiếu theo dõi cần thanh toán)
    public function postPaymentFollowup()
    {
        $selected     = $this->request->getPost('selected'); // mảng id của phiếu theo dõi được chọn
        $payment_date = $this->request->getPost('payment_date');
        $total        = 0;

        foreach ($selected as $voucher_id) {
            $voucher = $this->cashJournalModel->find($voucher_id);
            $settled = $this->cashJournalModel->getSettledAmount($voucher_id);
            $total  += ($voucher['amount'] - $settled);
        }

        // Tạo phiếu thanh toán công nợ
        $paymentData = [
            'created_date'     => $payment_date,
            'amount'           => $total,
            'description'      => 'Thanh toán công nợ theo danh sách phiếu theo dõi',
            'transaction_type' => 'chi',
            'is_followup'      => 0,
            'created_by'       => $this->session->userId,
        ];
        $paymentVoucherId = $this->cashJournalModel->insert($paymentData);

        // Tạo liên kết thanh toán cho từng phiếu theo dõi được chọn
        foreach ($selected as $voucher_id) {
            $voucher  = $this->cashJournalModel->find($voucher_id);
            $settled  = $this->cashJournalModel->getSettledAmount($voucher_id);
            $remaining = $voucher['amount'] - $settled;
            $settlement = [
                'payment_voucher_id'  => $paymentVoucherId,
                'followup_voucher_id' => $voucher_id,
                'settlement_amount'   => $remaining,
            ];
            $this->debtSettlementModel->insert($settlement);
        }
        return redirect()->to('customs-expense/list-followup');
    }

    // 9.3.3 - GET: Hiển thị form lập phiếu thanh toán công nợ không theo phiếu theo dõi
    public function getPaymentNonFollowup()
    {
        return $this->render(); // View: getPaymentNonFollowup.php
    }

    // 9.3.3 - POST: Xử lý thanh toán công nợ không theo phiếu theo dõi (tự động theo FIFO)
    public function postPaymentNonFollowup()
    {
        $payment_date = $this->request->getPost('payment_date');
        $amount       = $this->request->getPost('amount');
        $description  = $this->request->getPost('description');

        // Tạo phiếu thanh toán công nợ
        $paymentData = [
            'created_date'     => $payment_date,
            'amount'           => $amount,
            'description'      => $description,
            'transaction_type' => 'chi',
            'is_followup'      => 0,
            'created_by'       => $this->session->userId,
        ];
        $paymentVoucherId = $this->cashJournalModel->insert($paymentData);

        // Tự động lựa chọn theo tiêu chuẩn FIFO các phiếu theo dõi chưa thanh toán
        $followups = $this->cashJournalModel->getUnsettledFollowupsFIFO();
        $remaining = $amount;
        foreach ($followups as $voucher) {
            if ($remaining <= 0) break;
            $settled  = $this->cashJournalModel->getSettledAmount($voucher['id']);
            $due      = $voucher['amount'] - $settled;
            if ($due <= 0) continue;
            $settle_amount = min($due, $remaining);
            $settlement = [
                'payment_voucher_id'  => $paymentVoucherId,
                'followup_voucher_id' => $voucher['id'],
                'settlement_amount'   => $settle_amount,
            ];
            $this->debtSettlementModel->insert($settlement);
            $remaining -= $settle_amount;
        }
        return redirect()->to('customs-expense/list-followup');
    }

    /* ---------------------------- 9.4 Danh sách phiếu theo dõi ---------------------------- */

    // GET: Hiển thị danh sách phiếu theo dõi (lấy từ bảng custom_purposes)
    public function getListFollowup()
    {
        $followups = $this->purposeModel->findAll();
        $this->assign('followups', $followups);
        return $this->render(); // View: getListFollowup.php
    }

    /* ---------------------------- 9.5 Danh sách phiếu thu/chi ---------------------------- */

    // GET: Hiển thị danh sách phiếu thu/chi
    public function getListCashJournal()
    {
        $journals = $this->cashJournalModel->findAll();
        // Phân chia số tiền thu và chi cho từng phiếu giao dịch
        foreach ($journals as &$journal) {
            if ($journal['transaction_type'] == 'thu') {
                $journal['thu'] = $journal['amount'];
                $journal['chi'] = '';
            } else {
                $journal['thu'] = '';
                $journal['chi'] = $journal['amount'];
            }
        }
        $this->assign('journals', $journals);
        return $this->render(); // View: getListCashJournal.php
    }

    /* ---------------------------- 9.5 Quản lý danh mục mục đích ---------------------------- */

    // GET: Hiển thị danh sách mục đích
    public function getManagePurposes()
    {
        $purposes = $this->purposeModel->findAll();
        $this->assign('purposes', $purposes);
        return $this->render(); // View: getManagePurposes.php
    }

    /* ---------------------------- 9.6 Báo cáo tổng quan tình hình quỹ ---------------------------- */

    // GET: Hiển thị báo cáo tổng quan tình hình quỹ
    public function getReportOverview()
    {
        $totalThu = $this->cashJournalModel->getTotalByType('thu');
        $totalChi = $this->cashJournalModel->getTotalByType('chi');
        $totalFund = $totalThu - $totalChi;
        $totalDebt = $this->cashJournalModel->getTotalUnsettledFollowup();
        $this->assign('totalFund', $totalFund);
        $this->assign('totalDebt', $totalDebt);
        return $this->render(); // View: getReportOverview.php
    }
}
