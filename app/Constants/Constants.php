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
} 