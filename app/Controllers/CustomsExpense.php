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

    // Constants for pagination
    const DEFAULT_PER_PAGE = 10;
    const PER_PAGE_OPTIONS = [10, 20, 50, 100];

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
        // Lấy ngày hiện tại định dạng YYYY-MM-DD
        $currentDate = date('Y-m-d');
        $this->assign('current_date', $currentDate);
    
        // Lấy danh sách tên mục đích (distinct purpose_name) từ bảng custom_purposes để gợi ý
        $suggestions = $this->purposeModel
                            ->select('purpose_name')
                            ->distinct()
                            ->findAll();
        $this->assign('purpose_suggestions', $suggestions);
    
        // Render view theo tên method (getAddFollowup.php)
        return $this->render();
    }

    // POST: Xử lý lưu phiếu theo dõi mới (postAddFollowup)
    public function postAddFollowup()
    {
        // Lấy ngày lập phiếu từ form input
        $voucher_date = $this->request->getPost('voucher_date');

        // Sinh tự động số phiếu (voucher_number)
        $voucherNumber = $this->purposeModel->getNextVoucherNumber($voucher_date);

        $data = [
            'voucher_date'    => $voucher_date,
            'purpose_name'    => $this->request->getPost('purpose_name'),
            'amount'          => $this->request->getPost('amount'),
            'remaining_amount'=> $this->request->getPost('amount'),
            'description'     => $this->request->getPost('description'),
            'created_by'      => $this->session->userId,
            'voucher_number'  => $voucherNumber,
        ];
        $this->purposeModel->insert($data);
        return redirect()->to('customs-expense/list-followup');
    }

    /* ---------------------------- Sửa phiếu theo dõi ---------------------------- */
    
    // GET: Hiển thị form sửa phiếu theo dõi
    public function getEditFollowup($id)
    {
        // Lấy phiếu theo dõi từ bảng custom_purposes thông qua PurposeModel
        $followup = $this->purposeModel->find($id);
        if (!$followup) {
            session()->setFlashdata('error', 'Không tìm thấy phiếu cần sửa.');
            return redirect()->to('customs-expense/list-followup');
        }
        
        // Kiểm tra phiếu theo dõi đã được sử dụng trong bảng custom_debt_settlements hay chưa
        // Sử dụng method getSettlementsByPurpose() của DebtSettlementModel (quan hệ mới: followup_voucher_id → custom_purposes.id)
        $settlements = $this->debtSettlementModel->getSettlementsByPurpose($id);
        $isUsed = !empty($settlements);
        
        // Định dạng ngày lập phiếu: từ yyyy-mm-dd sang dd-mm-yyyy
        $voucher_date = date('d-m-Y', strtotime($followup['voucher_date']));
        // Định dạng số tiền theo quy tắc Việt Nam (không có phần lẻ, phân cách phần ngàn bằng dấu chấm)
        $formattedAmount = number_format($followup['amount'], 0, ',', '.');
        
        // Assign từng biến đơn ra view
        $this->assign('id', $followup['id']);
        $this->assign('voucher_date', $voucher_date);
        $this->assign('purpose_name', $followup['purpose_name']);
        $this->assign('amount', $formattedAmount);
        $this->assign('description', $followup['description']);
        
        // Nếu phiếu đã được sử dụng, không cho phép cập nhật số tiền
        if ($isUsed) {
            $this->assign('readonly', 'readonly');
            $this->assign('amount_note', 'Số tiền không thể cập nhật vì phiếu đã được thanh toán');
        } else {
            $this->assign('readonly', '');
            $this->assign('amount_note', '');
        }
        
        // Lấy danh sách gợi ý tên mục đích từ custom_purposes (distinct purpose_name)
        $suggestions = $this->purposeModel->select('purpose_name')->distinct()->findAll();
        $this->assign('purpose_suggestions', $suggestions);
        
        return $this->render(); // Sẽ render file view: getEditFollowup.php
    }
    

    // POST: Xử lý cập nhật phiếu theo dõi
    public function postEditFollowup($id)
    {
        // Kiểm tra xem phiếu theo dõi đã được sử dụng trong custom_debt_settlements hay chưa
        $settlements = $this->debtSettlementModel->getSettlementsByPurpose($id);
        if (!empty($settlements)) {
            // Nếu đã được sử dụng, giữ nguyên số tiền gốc trong cơ sở dữ liệu
            $original = $this->purposeModel->find($id);
            $updatedAmount = $original['amount'];
            $updatedRemaining = $original['remaining_amount'];
        } else {
            // Nếu chưa được sử dụng, lấy số tiền mới nhập từ form
            $updatedAmount = $this->request->getPost('amount');
            $updatedRemaining = $updatedAmount;
        }
    
        // Lấy các trường cập nhật từ form
        $data = [
            'voucher_date'      => $this->request->getPost('voucher_date'),
            'purpose_name'      => $this->request->getPost('purpose_name'),
            'amount'            => $updatedAmount,
            'remaining_amount'  => $updatedRemaining,
            'description'       => $this->request->getPost('description')
        ];
    
        if ($this->purposeModel->update($id, $data)) {
            session()->setFlashdata('success', 'Cập nhật phiếu thành công.');
        } else {
            session()->setFlashdata('error', 'Cập nhật phiếu thất bại.');
        }
        return redirect()->to('customs-expense/list-followup');
    }
    
    

    /**
     * GET: Xoá phiếu theo dõi.
     * Chỉ cho phép xoá nếu phiếu chưa xuất hiện ở custom_cash_journals.purpose_id.
     *
     * @param int $id ID của phiếu theo dõi trong bảng custom_purposes
     * @return Response Redirect về danh sách phiếu theo dõi
     */
    public function getDeleteFollowup($id)
    {
        // Lấy danh sách settlement của phiếu theo dõi (theo quan hệ: custom_debt_settlements.followup_voucher_id → custom_purposes.id)
        $settlements = $this->debtSettlementModel->getSettlementsByPurpose($id);
        
        // Nếu phiếu đã được sử dụng (settlements không rỗng), không cho xoá
        if (!empty($settlements)) {
            session()->setFlashdata('error', 'Phiếu đã được sử dụng, không thể xoá.');
            return redirect()->to('customs-expense/list-followup');
        }
        
        // Nếu chưa sử dụng, xoá phiếu theo dõi
        $this->purposeModel->delete($id);
        session()->setFlashdata('success', 'Xoá phiếu thành công.');
        return redirect()->to('customs-expense/list-followup');
    }
    

    // GET: Xem chi tiết phiếu theo dõi
    public function getDetailFollowup($id)
    {
        // Lấy phiếu theo dõi từ bảng custom_purposes
        $followup = $this->purposeModel->find($id);
        if (!$followup) {
            session()->setFlashdata('error', 'Không tìm thấy phiếu theo dõi.');
            return redirect()->to('customs-expense/list-followup');
        }
        
        // Lấy thông tin người lập phiếu từ UserModel
        $voucherCreator = $this->userModel->find($followup['created_by']);
        
        // Lấy danh sách settlement (các công nợ đã trả) từ DebtSettlementModel
        $settlements = $this->debtSettlementModel->getSettlementsByPurpose($id);
        
        // Định dạng các giá trị của phiếu theo dõi theo quy tắc Việt Nam
        $voucher_number = $followup['voucher_number'];
        $voucher_date   = date('d-m-Y', strtotime($followup['voucher_date']));
        $purpose_name   = $followup['purpose_name'];
        $amount         = number_format($followup['amount'], 0, ',', '.');
        $description    = $followup['description'];
        $settled_amount = 0;
        $remaining_amount = $followup['remaining_amount'];

        // Assign từng biến đơn (không dùng dấu chấm) ra view
        $this->assign('voucher_number', $voucher_number);
        $this->assign('voucher_date', $voucher_date);
        $this->assign('purpose_name', $purpose_name);
        $this->assign('amount', $amount);
        $this->assign('description', $description);

        // Assign thông tin người lập phiếu
        $this->assign('creator_fullname', $voucherCreator['fullname'] ?? '');
        $this->assign('creator_username', $voucherCreator['username'] ?? '');

        // Với mỗi settlement, định dạng số tiền
        foreach ($settlements as $key => $settlement) {
            $settled_amount += $settlement['settlement_amount'];    
            $settlements[$key]['settlement_amount'] = number_format($settlement['settlement_amount'], 0, ',', '.');
        }
        $this->assign('settlements', $settlements);
        $this->assign('settled_amount', number_format($settled_amount, 0, ',', '.'));
        $this->assign('remaining_amount', number_format($remaining_amount, 0, ',', '.'));

        return $this->render(); // Sử dụng file view: getDetailFollowup.php
    }

    /* ---------------------------- 9.2 Lập phiếu thu tiền ---------------------------- */

    // GET: Hiển thị form lập phiếu thu tiền
    public function getAddCashReceipt()
    {
        // Lấy ngày hiện tại theo định dạng YYYY-MM-DD
        $currentDate = date('Y-m-d');
        $this->assign('current_date', $currentDate);
        return $this->render();
    }

    // POST: Xử lý lưu phiếu thu tiền khi form được submit
    public function postAddCashReceipt()
    {
        $data = [
            'created_date'     => $this->request->getPost('created_date'),
            'amount'           => $this->request->getPost('amount'),
            'description'      => $this->request->getPost('description'),
            'transaction_type' => 'thu',
            'created_by'       => $this->session->userId,
        ];
        $this->cashJournalModel->insert($data);
        return redirect()->to('customs-expense/list-cash-journal');
    }

    /* ---------------------------- 9.3 Thanh toán công nợ ---------------------------- */

    // 9.3.1 - GET: Hiển thị danh sách phiếu theo dõi chưa thanh toán
    public function getPaymentSelection()
    {
        // Lấy tất cả phiếu theo dõi từ custom_purposes
        $followups = $this->purposeModel->findAll();
        $unsettledFollowups = [];
        
        // Với mỗi phiếu, tính số đã thanh toán và số chưa thanh toán
        foreach ($followups as $followup) {
            $settled = $this->cashJournalModel->getSettledAmount($followup['id']);
            // $followup['amount'] là giá trị số (chưa định dạng)
            $outstandingAmount = $followup['amount'] - $settled;
            
            if ($outstandingAmount > 0) {
                // Định dạng ngày theo dd-mm-yyyy
                $followup['voucher_date'] = date('d-m-Y', strtotime($followup['voucher_date']));
                // Định dạng số tiền gốc và số chưa thanh toán theo quy tắc Việt Nam
                $followup['amount'] = number_format($followup['amount'], 0, ',', '.');
                $followup['outstanding'] = number_format($outstandingAmount, 0, ',', '.');
                // Lưu giá trị raw để tính toán trên view
                $followup['raw_outstanding'] = $outstandingAmount;
                $unsettledFollowups[] = $followup;
            }
        }
        
        // Lấy ngày hiện tại cho trường thanh toán (định dạng YYYY-MM-DD vì là input date)
        $currentDate = date('Y-m-d');
        $this->assign('current_date', $currentDate);
        $this->assign('followups', $unsettledFollowups);
        
        return $this->render(); // View: getPaymentSelection.php
    }
    

    // 9.3.2 - POST: Lập phiếu thanh toán công nợ theo phiếu theo dõi (sau khi chọn các phiếu theo dõi cần thanh toán)
    public function postPaymentFollowup()
    {
        // Lấy dữ liệu từ form
        $payment_date = $this->request->getPost('payment_date');
        $totalPayment = $this->request->getPost('total_payment');
        $description  = $this->request->getPost('description');
    
        // Tạo phiếu thanh toán (phiếu chi) mới trong custom_cash_journals
        $paymentData = [
            'created_date'     => $payment_date,
            'amount'           => $totalPayment,
            'description'      => $description,
            'transaction_type' => 'chi',
            'is_followup'      => 0,
            'created_by'       => $this->session->userId,
        ];
        $paymentVoucherId = $this->cashJournalModel->insert($paymentData);
    
        // Lấy danh sách phiếu theo dõi được chọn từ form (là mảng các id của phiếu theo dõi ở custom_purposes)
        $selected = $this->request->getPost('selected'); 
        $remaining = $totalPayment;
        
        foreach ($selected as $voucherId) {
            // Lấy phiếu theo dõi từ custom_purposes
            $followup = $this->purposeModel->find($voucherId);
            if (!$followup) {
                continue;
            }
            
            // Tính số tiền đã thanh toán cho phiếu theo dõi này qua DebtSettlementModel
            $settled = $this->debtSettlementModel->getTotalSettledAmount($voucherId);
            
            // Tính số tiền còn lại chưa thanh toán cho phiếu theo dõi
            $due = $followup['amount'] - $settled;
            if ($due <= 0) {
                continue;
            }
            
            // Số tiền thanh toán đối với phiếu này: không vượt quá số tiền chưa thanh toán của phiếu và tổng số tiền thanh toán còn lại
            $settle_amount = min($due, $remaining);
            $settlement = [
                'payment_voucher_id'  => $paymentVoucherId,
                'followup_voucher_id' => $voucherId,
                'settlement_amount'   => $settle_amount,
            ];
            $this->debtSettlementModel->insert($settlement);
            
            // Giảm số tiền thanh toán còn lại
            $remaining -= $settle_amount;
            
            // Cập nhật remaining_amount cho phiếu theo dõi: 
            // remaining_amount = amount - tổng settlement đã thanh toán (tính qua DebtSettlementModel)
            $newRemaining = $followup['amount'] - $this->debtSettlementModel->getTotalSettledAmount($voucherId);
            $this->purposeModel->update($voucherId, ['remaining_amount' => $newRemaining]);
            
            if ($remaining <= 0) {
                break;
            }
        }
        
        return redirect()->to('customs-expense/list-cash-journal');
    }
    
    

    // 9.3.3 - GET: Hiển thị form lập phiếu thanh toán công nợ không theo phiếu theo dõi
    public function getPaymentNonFollowup()
    {
        // Lấy ngày hiện tại theo định dạng YYYY-MM-DD
        $currentDate = date('Y-m-d');
        $this->assign('current_date', $currentDate);
        return $this->render();
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
        return redirect()->to('customs-expense/list-cash-journal');
    }

    /* ---------------------------- 9.4 Danh sách phiếu theo dõi ---------------------------- */

    // GET: Hiển thị danh sách phiếu theo dõi (lấy từ bảng custom_purposes)
    public function getListFollowup()
    {
        // Get total count of followups
        $totalFollowups = $this->purposeModel->countAll();

        // Sử dụng phương thức handlePagination từ BaseController
        $pagination = $this->handlePagination($totalFollowups);

        // Get paginated followups using customPaginate
        $followups = $this->purposeModel->customPaginate($pagination['perPage'], $pagination['page']);
    
        foreach ($followups as $key => $followup) {
            $formattedDate = date('d-m-Y', strtotime($followup['voucher_date']));
            $formattedAmount = number_format($followup['amount'], 0, ',', '.');
            $formattedRemaining = number_format($followup['remaining_amount'], 0, ',', '.');
    
            $followups[$key]['voucher_date'] = $formattedDate;
            $followups[$key]['amount'] = $formattedAmount;
            $followups[$key]['remaining_amount'] = $formattedRemaining;
    
            $settlements = $this->debtSettlementModel->getSettlementsByPurpose($followup['id']);
            if (empty($settlements)) {
                $followups[$key]['can_delete'] = [['can_delete_id' => $followup['id']]];
            } else {
                $followups[$key]['can_delete'] = [];
            }
        }
        
        $this->assign('followups', $followups);
        return $this->render();
    }
    
    
    
    
    /* ---------------------------- 9.5 Danh sách phiếu thu/chi ---------------------------- */

    // GET: Lấy danh sách phiếu thu/chi và hiển thị cùng cột mã phiếu theo dõi (nếu có)
    public function getListCashJournal()
    {
        // Get total count of journals
        $totalJournals = $this->cashJournalModel->countAll();

        // Sử dụng phương thức handlePagination từ BaseController
        $pagination = $this->handlePagination($totalJournals);

        // Get paginated journals using customPaginate
        $journals = $this->cashJournalModel->customPaginate($pagination['perPage'], $pagination['page']);
        
        foreach ($journals as $key => $journal) {
            // Định dạng ngày: từ yyyy-mm-dd sang dd-mm-yyyy
            $journals[$key]['created_date'] = date('d-m-Y', strtotime($journal['created_date']));
            // Định dạng số tiền theo quy tắc Việt Nam (không phần lẻ, phân cách phần ngàn bằng dấu chấm)
            $formattedAmount = number_format($journal['amount'], 0, ',', '.');
            
            // Gán giá trị vào các cột Thu và Chi tùy theo transaction_type
            if ($journal['transaction_type'] == 'thu') {
                $journals[$key]['thu'] = $formattedAmount;
                $journals[$key]['chi'] = '';
                // Không có liên kết phiếu theo dõi
                $journals[$key]['voucher_link'] = '';
            } else {  // Loại 'chi'
                $journals[$key]['thu'] = '';
                $journals[$key]['chi'] = $formattedAmount;
                // Lấy danh sách phiếu theo dõi liên kết với phiếu chi này qua custom_debt_settlements
                $followupVouchers = $this->debtSettlementModel->getFollowupVouchersByPayment($journal['id']);
                $links = [];
                if (!empty($followupVouchers)) {
                    foreach ($followupVouchers as $fv) {
                        // Tạo link cho mỗi phiếu theo dõi, mở cửa sổ mới (target="_blank")
                        $url = site_url("customs-expense/detail-followup/{$fv['followup_id']}");
                        $links[] = '<a href="' . $url . '" target="_blank">' . $fv['voucher_number'] . '</a>';
                    }
                    // Ghép các link lại, cách nhau bởi dấu phẩy
                    $journals[$key]['voucher_link'] = implode(', ', $links);
                } else {
                    $journals[$key]['voucher_link'] = '';
                }
            }
            
            // Nếu có các trường mục đích và mô tả đã được lưu ở cash journal (nếu có), assign hoặc để rỗng
            $journals[$key]['purpose_name'] = $journal['purpose_name'] ?? '';
            $journals[$key]['description'] = $journal['description'] ?? '';
        }
        
        $this->assign('journals', $journals);
        return $this->render();
    }
    
    


    // GET: Hiển thị trang chọn loại phiếu thu/chi
    public function getChooseCashVoucher()
    {
        return $this->render(); // View: getChooseCashVoucher.php
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
