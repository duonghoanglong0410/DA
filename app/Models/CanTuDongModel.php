<?php

namespace App\Models;

use App\Models\BaseModel; // Kế thừa BaseModel

class CanTuDongModel extends BaseModel
{
    protected $table            = 'can_tu_dong'; // Tên bảng
    
    // Disable timestamps since the table doesn't have created_at and updated_at columns
    protected $useTimestamps = false;
    
    // Biến để lưu trữ từ khóa tìm kiếm
    protected $searchTerm = '';

    // Các cột được phép thao tác (thêm tất cả các cột cần thiết)
    protected $allowedFields    = [
        'Msp', 'Tenkhachhang', 'Soxe', 'Loaihang', 'KLcotai', 'KLkhongtai', 
        'KLhang', 'Dongia', 'KLgo', 'Nguoican', 'Sophieu', 'Thang', 'Nam', 
        'Ghichu', 'phantram', 'KLtru', 'Sophieuin', 'Thanhtien', 
        'KLkhongtaiR', 'KLcotaiR', 'KLhangR', 'Tenlaixe', 'Bangchu', 
        'TNgaycan', 'Giocantruoc', 'Giocansau', 'Ngaycan', 'solanin', 
        'lanin', 'chedo', 'purchase_yard_id', 'is_receipted' // Đã thêm cột is_receipted
    ];
    
    /**
     * Override _applyWhereConditions từ BaseModel để thêm xử lý searchTerm và join
     *
     * @param object $builder    Builder instance.
     * @param array  $where      Điều kiện lọc.
     * @param string $searchTerm Từ khóa tìm kiếm (tùy chọn).
     * @return object           Builder instance.
     */
    protected function _applyWhereConditions($builder, $where = [], $searchTerm = null)
    {
        // Áp dụng searchTerm nếu được cung cấp, hoặc sử dụng giá trị đã lưu trữ
        $searchTermToUse = $searchTerm !== null ? $searchTerm : $this->searchTerm;
        
        // Thêm join với bảng purchase_yards
        $this->join('purchase_yards', 'purchase_yards.id = can_tu_dong.purchase_yard_id', 'left');
        // Áp dụng điều kiện tìm kiếm LIKE
        if (!empty($searchTermToUse)) {
            $this->groupStart()
                   ->like('can_tu_dong.Soxe', $searchTermToUse, 'both')
                   ->orLike('can_tu_dong.Ghichu', $searchTermToUse, 'both')
                   ->orLike('can_tu_dong.Tenlaixe', $searchTermToUse, 'both')
                   ->orLike('can_tu_dong.Tenkhachhang', $searchTermToUse, 'both')
                   ->groupEnd();
        }
        
        // Gọi phương thức _applyWhereConditions của lớp cha
        return parent::_applyWhereConditions($this, $where);
    }

    /**
     * Override customPaginate từ BaseModel và thêm searchTerm
     *
     * @param int    $perPage     Số mục mỗi trang.
     * @param int    $page        Số trang hiện tại.
     * @param string $orderBy     Cột và hướng sắp xếp.
     * @param array  $where       Điều kiện lọc cơ bản.
     * @param string $searchTerm  Từ khóa tìm kiếm (Soxe, Ghichu, Tenlaixe).
     * @return array             Danh sách lịch sử cân.
     */
    public function customPaginate($perPage, $page, $orderBy = '', $where = [], $searchTerm = '')
    {
        $perPage = intval($perPage);
        $page = intval($page);
        
        // Lưu searchTerm vào biến instance
        $this->searchTerm = $searchTerm;
        
        // Thêm các trường cần hiển thị
        $this->select('can_tu_dong.*, purchase_yards.yard_code, purchase_yards.yard_name');
        
        // Mặc định orderBy nếu không có
        if (empty($orderBy)) {
            $orderBy = 'Ngaycan DESC';
        }
        
        // Sử dụng phương thức của BaseModel để xử lý phần còn lại
        return parent::customPaginate($perPage, $page, $orderBy, $where);
    }
    
    /**
     * Override customPaginateCountAll từ BaseModel và thêm searchTerm
     *
     * @param array  $where       Điều kiện lọc cơ bản.
     * @param string $searchTerm  Từ khóa tìm kiếm.
     * @return int               Tổng số bản ghi.
     */
    public function customPaginateCountAll($where = [], $searchTerm = '')
    {
        // Lưu searchTerm vào biến instance
        $this->searchTerm = $searchTerm;
        
        // Chỉ cần select id để đếm
        $this->select('can_tu_dong.id');
        
        // Sử dụng phương thức của BaseModel để xử lý phần còn lại
        return parent::customPaginateCountAll($where);
    }
    
    /**
     * Thống kê tổng KL không tải, có tải, KL hàng, tổng thành tiền nhóm theo bãi và loại hàng
     *
     * @param array  $where       Điều kiện lọc cơ bản
     * @param string $searchTerm  Từ khóa tìm kiếm
     * @return array             Kết quả thống kê
     */
    public function getStatsByYardAndType($where = [], $searchTerm = '')
    {
        $this->select('
                purchase_yards.yard_code,
                purchase_yards.yard_name,
                can_tu_dong.Loaihang,
                SUBSTRING(can_tu_dong.Msp, 1, 2) as Msp,
                COUNT(*) as total_records,
                SUM(can_tu_dong.KLkhongtai) as total_KLkhongtai,
                SUM(can_tu_dong.KLcotai) as total_KLcotai,
                SUM(can_tu_dong.KLhang) as total_KLhang,
                SUM(can_tu_dong.Thanhtien) as total_Thanhtien
            ');
        
        // Áp dụng điều kiện where và searchTerm
        $this->_applyWhereConditions($this, $where, $searchTerm);
        
        // Nhóm kết quả theo bãi và loại hàng và loại phiếu
        $this->groupBy('purchase_yards.yard_code, purchase_yards.yard_name, can_tu_dong.Loaihang, SUBSTRING(can_tu_dong.Msp, 1, 2)');
        
        // Sắp xếp kết quả
        $this->orderBy('purchase_yards.yard_name ASC, can_tu_dong.Loaihang ASC, Msp ASC');
        
        // Thực hiện truy vấn và trả về kết quả
        return $this->get()->getResultArray();
    }
    
    /**
     * Các phương thức tiện ích để duy trì khả năng tương thích ngược
     */
    
    public function getPaginatedScaleHistory($perPage, $page, $orderBy = 'Ngaycan DESC', $where = [], $searchTerm = '')
    {
        return $this->customPaginate($perPage, $page, $orderBy, $where, $searchTerm);
    }
    
    public function countScaleHistory($where = [], $searchTerm = '')
    {
        return $this->customPaginateCountAll($where, $searchTerm);
    }

    /**
     * Lấy dữ liệu cân theo loại phiếu trong ngày theo danh sách bãi được phân quyền
     *
     * @param string $today Ngày hiện tại (format Y-m-d)
     * @param array $yardIds Danh sách ID bãi được phân quyền
     * @param string $receiptType Loại phiếu (NK, HK, XK, CT)
     * @return array
     */
    public function getTodayReceiptDataByYardIds($today, $yardIds, $receiptType = 'NK')
    {
        if (empty($yardIds)) {
            return [];
        }
        
        // Đảm bảo $yardIds là một mảng
        if (!is_array($yardIds)) {
            $yardIds = [$yardIds];
        }
        
        // Chọn các cột cần thiết
        $this->select('can_tu_dong.*, purchase_yards.yard_name, purchase_yards.yard_code');
        
        // Join với bảng purchase_yards để lấy thông tin bãi
        $this->join('purchase_yards', 'purchase_yards.id = can_tu_dong.purchase_yard_id', 'left');
        
        // Tìm theo ngày
        $this->where('can_tu_dong.Ngaycan', $today);
        
        // Tìm theo Msp với loại phiếu tương ứng
        $this->where('can_tu_dong.Msp', $receiptType);
        
        // Tìm theo danh sách bãi
        $this->whereIn('can_tu_dong.purchase_yard_id', $yardIds);
        
        // Chỉ lấy các phiếu cân chưa được lập phiếu nhập hàng
        $this->where('can_tu_dong.is_receipted', 0);

        $this->orderBy('can_tu_dong.Loaihang ASC');
        
        // Thực hiện truy vấn
        return $this->findAll();
    }
    
    /**
     * Phương thức cũ, giữ lại để tương thích ngược
     * @deprecated Sử dụng getTodayReceiptDataByYardIds() với tham số $receiptType = 'NK'
     */
    public function getTodayNKDataByYardIds($today, $yardIds)
    {
        return $this->getTodayReceiptDataByYardIds($today, $yardIds, 'NK');
    }
    
    /**
     * Đánh dấu phiếu cân đã được lập phiếu xuất hàng
     * 
     * @param array $scaleIds Danh sách ID phiếu cân cần đánh dấu
     * @return bool Kết quả cập nhật
     */
    public function markAsReceipted($scaleIds)
    {
        if (empty($scaleIds)) {
            return false;
        }
        
        // Nếu scaleIds là một chuỗi, chuyển thành mảng
        if (is_string($scaleIds)) {
            $scaleIds = explode(',', $scaleIds);
        }
        
        // Lọc các ID hợp lệ
        $validIds = [];
        foreach ($scaleIds as $id) {
            if (is_numeric($id) && $id > 0) {
                $validIds[] = (int)$id;
            }
        }
        
        if (empty($validIds)) {
            return false;
        }
        
        // Ghi log để debug
        log_message('debug', 'CanTuDongModel::markAsReceipted - Đánh dấu phiếu cân: ' . implode(', ', $validIds));
        
        // Cập nhật trạng thái is_receipted của các phiếu cân
        $this->whereIn('id', $validIds);
        
        return $this->update(null, ['is_receipted' => 1]);
    }
}


