<?php

namespace App\Constants;

class Constants
{
    // Số bản ghi mặc định trên mỗi trang
    const DEFAULT_PER_PAGE = 10;
    
    // Các tùy chọn số bản ghi trên mỗi trang
    const PER_PAGE_OPTIONS = [10, 20, 50, 100];
    
    // Số lượng trang hiển thị trước và sau trang hiện tại trong phân trang
    const PAGINATION_LINKS_PER_SIDE = 2;
    
    // Chọn phiếu cân xuất kho: true = chỉ chọn 1 dòng, false = chọn nhiều dòng
    const SINGLE_SCALE_SELECTION = true;
    
    // Số ngày hiển thị lịch sử phiếu nhập hàng
    const RECEIPT_HISTORY_DAYS = 7;
    
    // ID mặc định của loại tiền tệ
    const DEFAULT_CURRENCY_ID = 2;
} 