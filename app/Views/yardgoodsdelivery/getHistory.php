<div class="container-fluid mt-3">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-11">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Danh sách phiếu xuất ({history_days} ngày gần đây)</h5>
                </div>
                
                <!-- Filter options -->
                <div class="card-body pb-0">
                    <div class="row">
                        <div class="col-md-6 mb-3 yards-column {yards_display_class}">
                            <div class="form-group">
                                <label for="yard-filter" class="form-label">Lọc theo bãi:</label>
                                <select id="yard-filter" class="form-select">
                                    <option value="">Tất cả bãi</option>
                                    {yard_options}
                                        <option value="{yard_id}">{yard_name}</option>
                                    {/yard_options}
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
                
                {no_data_message}
                <div class="card-body">
                    <div class="alert alert-info">{message}</div>
                </div>
                {/no_data_message}
                
                <div class="table-responsive {delivery_content_class}">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th class="yards-column {yards_display_class}">Bãi</th>
                                <th>Biển số xe</th>
                                <th>Loại hàng</th>
                                <th class="desktop-weight-col text-end">KL hàng (kg)</th>
                                <th class="desktop-price-col text-end">Đơn giá</th>
                                <th class="desktop-price-col text-end">Thành tiền</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            {deliveries}
                            <tr class="data-row" data-yard-id="{yard_id}">
                                <td>{delivery_date}</td>
                                <td class="yards-column {yards_display_class}">{yard_name}</td>
                                <td>{vehicle_number}</td>
                                <td>{category_name}</td>
                                <td class="desktop-weight-col text-end">{weight_formatted}</td>
                                <td class="desktop-price-col text-end">{unit_price_formatted} {currency_symbol}</td>
                                <td class="desktop-price-col text-end">{total_amount_formatted} {currency_symbol}</td>
                                <td class="text-center">
                                    <a href="{site_url}yard-goods-delivery/create-adjustment/{id}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                </td>
                            </tr>
                            <tr class="mobile-price-row" data-yard-id="{yard_id}">
                                <td colspan="4" class="mobile-price-cell">
                                    <div class="mobile-price-content">
                                        <div class="mobile-detail-item">
                                            <span class="mobile-price-label">KL hàng:</span>
                                            <span>{weight_formatted} kg</span>
                                        </div>
                                        <div class="mobile-detail-item">
                                            <span class="mobile-price-label">Đơn giá:</span>
                                            <span>{unit_price_formatted} {currency_symbol}</span>
                                        </div>
                                        <div class="mobile-detail-item">
                                            <span class="mobile-price-label">Thành tiền:</span>
                                            <span>{total_amount_formatted} {currency_symbol}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            {/deliveries}
                        </tbody>
                    </table>
                </div>
                
                <!-- Back button at bottom right -->
                <div class="card-footer mt-3 d-flex justify-content-start">
                    <a href="{site_url}yard-goods-delivery" class="btn btn-secondary btn-lg py-3">Quay lại</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for viewing delivery details -->
<div class="modal fade" id="deliveryDetailModal" tabindex="-1" role="dialog" aria-labelledby="deliveryDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="deliveryDetailModalLabel">Chi tiết phiếu xuất kho</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="deliveryDetailContent">
                <!-- Content will be loaded dynamically -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Đang tải...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for collecting payment -->
<div class="modal fade" id="collectPaymentModal" tabindex="-1" role="dialog" aria-labelledby="collectPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="collectPaymentModalLabel">Thu tiền phiếu xuất kho</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="paymentForm" class="form-box getDetail">
                    <input type="hidden" id="payment_trip_id" name="trip_id">
                    <input type="hidden" id="payment_delivery_id" name="delivery_id">
                    
                    <div class="form-group row">
                        <label for="payment_date" class="col-sm-3 col-form-label">Ngày thu tiền:</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="payment_date" name="payment_date" required value="{today_date}">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="payment_amount" class="col-sm-3 col-form-label">Số tiền thu:</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="payment_amount" name="payment_amount" required>
                            <small class="form-text text-muted">Tổng số tiền cần thu: <span id="total_debt_amount">0</span></small>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="payment_method" class="col-sm-3 col-form-label">Phương thức thanh toán:</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="1">Tiền mặt</option>
                                <option value="2">Chuyển khoản</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="payment_note" class="col-sm-3 col-form-label">Ghi chú:</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="payment_note" name="payment_note" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="savePayment">Lưu phiếu thu</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Filter by yard
    $('#yard-filter').change(function() {
        var yardId = $(this).val();
        
        // Show all rows first
        $('tbody tr').show();
        
        // Apply yard filter if not empty
        if (yardId !== '') {
            $('tbody tr').each(function() {
                const rowYardId = $(this).data('yard-id');
                if (rowYardId && rowYardId != yardId) {
                    $(this).hide();
                }
            });
        }
    });

    // Handle view delivery button
    $('.view-delivery').click(function() {
        var deliveryId = $(this).data('delivery-id');
        var tripId = $(this).data('trip-id');
        
        // Clear previous content
        $('#deliveryDetailContent').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Đang tải...</span></div></div>');
        
        // Show modal
        $('#deliveryDetailModal').modal('show');
        
        // Load delivery details via AJAX
        $.ajax({
            url: '{site_url}yard-goods-delivery/get-delivery-detail',
            type: 'POST',
            data: {
                delivery_id: deliveryId,
                trip_id: tripId
            },
            dataType: 'json',
            success: function(response) {
                // THêm ghi chú để tránh lỗi
                if (response.success) {
                    // Populate modal with delivery details
                    $('#deliveryDetailContent').html(response.html);
                } else {
                    // Show error
                    $('#deliveryDetailContent').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                // Show error
                $('#deliveryDetailContent').html('<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại.</div>');
            }
        });
    });

    // Handle collect payment button
    $('.collect-payment').click(function() {
        var deliveryId = $(this).data('delivery-id');
        var tripId = $(this).data('trip-id');
        
        // Set values in form
        $('#payment_trip_id').val(tripId);
        $('#payment_delivery_id').val(deliveryId);
        
        // Show modal
        $('#collectPaymentModal').modal('show');
        
        // Load debt information via AJAX
        $.ajax({
            url: '{site_url}yard-goods-delivery/get-debt-info',
            type: 'POST',
            data: {
                trip_id: tripId
            },
            dataType: 'json',
            success: function(response) {
                // THêm ghi chú để tránh lỗi
                if (response.success) {
                    // Set debt amount
                    $('#total_debt_amount').text(response.total_debt_formatted);
                    $('#payment_amount').val(response.total_debt);
                    $('#payment_amount').attr('max', response.total_debt);
                } else {
                    // Show error
                    alert(response.message);
                    $('#collectPaymentModal').modal('hide');
                }
            },
            error: function() {
                // Show error
                alert('Có lỗi xảy ra khi tải thông tin công nợ. Vui lòng thử lại.');
                $('#collectPaymentModal').modal('hide');
            }
        });
    });

    // Handle save payment button
    $('#savePayment').click(function() {
        // Validate form
        if (!$('#paymentForm')[0].checkValidity()) {
            $('#paymentForm')[0].reportValidity();
            return;
        }
        
        // Disable button to prevent double submission
        $(this).prop('disabled', true);
        
        // Send payment data via AJAX
        $.ajax({
            url: '{site_url}yard-goods-delivery/save-payment',
            type: 'POST',
            data: $('#paymentForm').serialize(),
            dataType: 'json',
            success: function(response) {
                // THêm ghi chú để tránh lỗi
                if (response.success) {
                    // Show success message
                    alert(response.message);
                    // Close modal
                    $('#collectPaymentModal').modal('hide');
                    // Reload page
                    location.reload();
                } else {
                    // Show error
                    alert(response.message);
                    // Re-enable button
                    $('#savePayment').prop('disabled', false);
                }
            },
            error: function() {
                // Show error
                alert('Có lỗi xảy ra khi lưu phiếu thu. Vui lòng thử lại.');
                // Re-enable button
                $('#savePayment').prop('disabled', false);
            }
        });
    });
});
</script> 