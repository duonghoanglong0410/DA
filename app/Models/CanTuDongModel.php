<?php

namespace App\Models;

use App\Models\BaseModel; // Kế thừa BaseModel

class CanTuDongModel extends BaseModel
{
    protected $table            = 'can_tu_dong'; // Tên bảng
    
    // Biến để lưu trữ từ khóa tìm kiếm
    protected $searchTerm = '';

    // Các cột được phép thao tác (thêm tất cả các cột cần thiết)
    protected $allowedFields    = [
        'Msp', 'Tenkhachhang', 'Soxe', 'Loaihang', 'KLcotai', 'KLkhongtai', 
        'KLhang', 'Dongia', 'KLgo', 'Nguoican', 'Sophieu', 'Thang', 'Nam', 
        'Ghichu', 'phantram', 'KLtru', 'Sophieuin', 'Thanhtien', 
        'KLkhongtaiR', 'KLcotaiR', 'KLhangR', 'Tenlaixe', 'Bangchu', 
        'TNgaycan', 'Giocantruoc', 'Giocansau', 'Ngaycan', 'solanin', 
        'lanin', 'chedo', 'purchase_yard_id' // Đã thêm cột khóa ngoại
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
        $builder->join('purchase_yards', 'purchase_yards.id = can_tu_dong.purchase_yard_id', 'left');
        
        // Áp dụng điều kiện tìm kiếm LIKE
        if (!empty($searchTermToUse)) {
            $builder->groupStart()
                   ->like('can_tu_dong.Soxe', $searchTermToUse, 'both')
                   ->orLike('can_tu_dong.Ghichu', $searchTermToUse, 'both')
                   ->orLike('can_tu_dong.Tenlaixe', $searchTermToUse, 'both')
                   ->groupEnd();
        }
        
        // Gọi phương thức _applyWhereConditions của lớp cha
        return parent::_applyWhereConditions($builder, $where);
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
}
