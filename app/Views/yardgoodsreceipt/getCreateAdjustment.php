<div class="container-fluid mt-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Đề nghị chỉnh sửa phiếu nhập hàng</h5>
                </div>
                <div class="card-body autofocus">
                    {is_editing_existing}
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> Bạn đang chỉnh sửa phiếu điều chỉnh đã tạo trước đó chưa được duyệt.
                    </div>
                    {/is_editing_existing}
                    
                    <form id="adjustmentForm" method="post" action="{site_url}yard-goods-receipt/save-adjustment">
                        <!-- Thông tin phiếu hiện tại -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">Thông tin phiếu hiện tại</h6>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <strong>Ngày:</strong> {receipt_input_date}
                                </div>
                                <div class="col-6">
                                    <strong>Bãi:</strong> {receipt_yard_name}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <strong>Biển số xe:</strong> {receipt_vehicle_number}
                                </div>
                                <div class="col-6">
                                    <strong>Loại hàng:</strong> {receipt_category_name}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6">
                                    <strong>Khối lượng:</strong> {receipt_weight_formatted} kg
                                </div>
                                <div class="col-6">
                                    <strong>Đơn giá:</strong> {receipt_unit_price_formatted}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <strong>Thành tiền:</strong> {receipt_total_amount_formatted}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Đề nghị chỉnh sửa -->
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2 mb-3">Đề nghị chỉnh sửa</h6>
                            
                            <div class="form-group mb-3">
                                <label for="new_weight" class="form-label">Khối lượng mới</label>
                                <input type="number" id="new_weight" name="new_weight" class="form-control" step="1" value="{new_weight}" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="new_unit_price" class="form-label">Đơn giá mới</label>
                                <input type="number" id="new_unit_price" name="new_unit_price" class="form-control" step="1" value="{new_unit_price}" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label">Thành tiền mới:</label>
                                    <span id="new_total_amount" class="fs-5 fw-bold">0</span>
                                </div>
                            </div>
                            
                            <!-- Hidden fields -->
                            <input type="hidden" id="receipt_id" name="receipt_id" value="{receipt_id}">
                            <input type="hidden" id="old_weight" name="old_weight" value="{receipt_weight}">
                            <input type="hidden" id="old_unit_price" name="old_unit_price" value="{receipt_unit_price}">
                            <input type="hidden" id="adjustment_id" name="adjustment_id" value="{adjustment_id}">
                        </div>
                        
                        <div class="mt-3 d-flex justify-content-between">
                            <a href="{site_url}yard-goods-receipt/history" class="btn btn-secondary btn-lg py-3">Quay lại</a>
                            <button type="submit" class="btn btn-primary btn-lg py-3">Lưu đề nghị</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        focusFirstInput();

        // Tính và cập nhật tổng tiền mới
        function updateNewTotalAmount() {
            const newWeight = Math.round(parseFloat($('#new_weight').val()) || 0);
            const newUnitPrice = Math.round(parseFloat($('#new_unit_price').val()) || 0);
            const newTotalAmount = newWeight * newUnitPrice;
            
            // Cập nhật giá trị đã làm tròn vào input
            $('#new_weight').val(newWeight);
            $('#new_unit_price').val(newUnitPrice);
            
            // Hiển thị tổng tiền với định dạng số của Việt Nam
            $('#new_total_amount').text(formatCurrency(newTotalAmount));
        }
        
        // Theo dõi sự thay đổi của khối lượng và đơn giá để cập nhật tổng tiền
        $('#new_weight, #new_unit_price').on('input change', function() {
            updateNewTotalAmount();
        });
        
        // Hàm định dạng số kiểu Việt Nam
        function formatCurrency(amount) {
            return amount.toLocaleString('vi-VN');
        }
        
        // Cập nhật tổng tiền khi trang tải xong
        updateNewTotalAmount();
        
        // Xử lý form submit
        $('#adjustmentForm').on('submit', function(e) {
            e.preventDefault();
            
            // Kiểm tra form trước khi submit
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }
            
            // Submit form bằng AJAX
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(data) {
                    // Xử lý kết quả trả về
                    if (data.success) {
                        alert(data.message);
                        // Chuyển về trang lịch sử sau khi lưu thành công
                        window.location.href = '{site_url}yard-goods-receipt/history';
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Đã xảy ra lỗi khi xử lý yêu cầu.');
                }
            });
        });
    });
</script> 