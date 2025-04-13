<div class="container-fluid mt-3">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-11">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Lịch sử phiếu nhập hàng ({history_days} ngày gần đây)</h5>
                </div>
                
                <!-- Filter options above the table -->
                <div class="card-body pb-0">
                    <div class="row">
                        {!yard_filter_html!}
                        <div class="col-md-{yard_filter_column_width} mb-3">
                            <div class="form-group">
                                <label for="filter_category" class="form-label">Lọc theo loại hàng:</label>
                                <select id="filter_category" class="form-select">
                                    <option value="all">Tất cả</option>
                                    {category_options}
                                    <option value="{id}">{name}</option>
                                    {/category_options}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- CSS for responsive table -->
                <style>
                    @media (max-width: 767px) {
                        .mobile-price-row {
                            display: table-row;
                            background-color: rgba(0, 0, 0, 0.02);
                            border-bottom: 1px solid rgba(0, 0, 0, 1) !important;
                        }
                        .mobile-price-cell {
                            border-top: none !important;
                            padding-top: 0 !important;
                            padding-bottom: 0px !important;
                            border-bottom: 1px solid rgba(0, 0, 0, 1) !important;
                        }
                        .desktop-price-col, .desktop-weight-col {
                            display: none;
                        }
                        .mobile-price-content {
                            display: flex;
                            justify-content: space-between;
                            width: 100%;
                            flex-wrap: wrap;
                            padding-bottom: 8px;
                            margin-bottom: 2px;
                        }
                        .mobile-detail-item {
                            flex: 1 1 30%;
                            min-width: 100px;
                            margin-bottom: 5px;
                        }
                        .mobile-price-label {
                            font-weight: bold;
                            margin-right: 10px;
                            display: block;
                            /* font-size: 0.85rem; */
                        }
                        .mobile-action-item {
                            flex: 0 0 100%;
                            margin-top: 8px;
                            text-align: center;
                        }
                    }
                    
                    @media (min-width: 768px) {
                        .mobile-price-row {
                            display: none;
                        }
                    }
                </style>
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                {!yard_column_header!}
                                <th>Biển số xe</th>
                                <th>Loại hàng</th>
                                <th class="desktop-weight-col text-end">KL hàng (kg)</th>
                                <th class="desktop-price-col text-end">Đơn giá</th>
                                <th class="desktop-price-col text-end">Thành tiền</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            {receipts}
                            <tr class="data-row" data-category-id="{category_id}" data-yard-id="{yard_id}">
                                <td>{input_date}</td>
                                {!yard_column_body!}
                                <td>{vehicle_number}</td>
                                <td>{category_name}</td>
                                <td class="desktop-weight-col text-end">{quantity_formatted}</td>
                                <td class="desktop-price-col text-end">{unit_price_formatted}</td>
                                <td class="desktop-price-col text-end">{total_amount_formatted}</td>
                                <td class="text-center">
                                    <a href="{site_url}yard-goods-receipt/create-adjustment/{id}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                </td>
                            </tr>
                            <tr class="mobile-price-row" data-category-id="{category_id}" data-yard-id="{yard_id}">
                                <td colspan="{colspan_count}" class="mobile-price-cell">
                                    <div class="mobile-price-content">
                                        <div class="mobile-detail-item">
                                            <span class="mobile-price-label">KL hàng:</span>
                                            <span>{quantity_formatted} kg</span>
                                        </div>
                                        <div class="mobile-detail-item">
                                            <span class="mobile-price-label">Đơn giá:</span>
                                            <span>{unit_price_formatted}</span>
                                        </div>
                                        <div class="mobile-detail-item">
                                            <span class="mobile-price-label">Thành tiền:</span>
                                            <span>{total_amount_formatted}</span>
                                        </div>
                                        <div class="mobile-action-item">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            {/receipts}
                            
                            <!-- Hiển thị thông báo nếu không có dữ liệu -->
                            {no_data_message}
                            <tr>
                                <td colspan="{colspan_count}" class="text-center">{message}</td>
                            </tr>
                            {/no_data_message}
                        </tbody>
                    </table>
                </div>
                
                <!-- Back button at bottom right -->
                <div class="card-footer mt-3 d-flex justify-content-end">
                    <a href="{site_url}yard-goods-receipt" class="btn btn-secondary btn-lg py-3">Quay lại</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Filter functionality
        $('#filter_yard, #filter_category').change(function() {
            const selectedYard = $('#filter_yard').val();
            const selectedCategory = $('#filter_category').val();
            
            // Show all rows first
            $('tbody tr').show();
            
            // Apply yard filter if not "all"
            if (selectedYard !== 'all' && selectedYard !== undefined) {
                $('tbody tr').each(function() {
                    const yardId = $(this).data('yard-id');
                    if (yardId && yardId != selectedYard) {
                        $(this).hide();
                    }
                });
            }
            
            // Apply category filter if not "all"
            if (selectedCategory !== 'all') {
                $('tbody tr').each(function() {
                    const categoryId = $(this).data('category-id');
                    if (categoryId && categoryId != selectedCategory) {
                        $(this).hide();
                    }
                });
            }
            
            // Check if any data rows are visible
            const visibleRows = $('tbody tr.data-row:visible').length;
            if (visibleRows === 0) {
                // Add a message row if no matching records
                if ($('#no-matching-records').length === 0) {
                    $('tbody').append('<tr id="no-matching-records"><td colspan="{colspan_count}" class="text-center">Không có phiếu nhập hàng nào phù hợp với điều kiện lọc.</td></tr>');
                } else {
                    $('#no-matching-records').show();
                }
            } else {
                // Hide the message row if we have matching records
                $('#no-matching-records').hide();
            }
        });
    });
</script> 