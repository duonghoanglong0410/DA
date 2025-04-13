<div class="container-fluid mt-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Đề nghị chỉnh sửa phiếu xuất kho</h5>
                </div>
                <div class="card-body">
                    <form id="adjustmentForm" method="post" action="{site_url}yard-goods-delivery/create-adjustment">
                        <input type="hidden" id="delivery_id" name="delivery_id" value="{delivery_id}">
                        <input type="hidden" id="reason" name="reason" value="Chỉnh sửa thông tin phiếu xuất">
                        
                        <h6 class="border-bottom pb-2 mb-3">Thông tin phiếu xuất kho hiện tại</h6>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Bãi:</strong> {yard_name}</div>
                            <div class="col-6"><strong>Biển số xe:</strong> {vehicle_number}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Loại hàng:</strong> {category_name}</div>
                            <div class="col-6"><strong>Ngày xuất:</strong> {delivery_date}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Khối lượng:</strong> {old_weight_formatted}</div>
                            <div class="col-6"><strong>Đơn giá:</strong> {old_unit_price_formatted} {currency_symbol}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-12"><strong>Thành tiền:</strong> {total_amount_formatted} {currency_symbol}</div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3">Đề nghị chỉnh sửa</h6>
                        <div class="form-group mb-3">
                            <label for="new_weight" class="form-label">Khối lượng mới:</label>
                            <input type="number" id="new_weight" name="new_weight" class="form-control" step="1" value="{old_weight}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="new_unit_price" class="form-label">Đơn giá mới:</label>
                            <input type="number" id="new_unit_price" name="new_unit_price" class="form-control" step="1" value="{old_unit_price}" required>
                        </div>
                        <div class="form-group mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">Thành tiền mới:</label>
                                <span id="new_total" class="fs-5 fw-bold">0</span>
                            </div>
                        </div>

                        <div class="mt-3 d-flex justify-content-between">
                            <a href="{site_url}yard-goods-delivery/history" class="btn btn-secondary">Quay lại</a>
                            <button type="submit" class="btn btn-primary">Gửi đề nghị chỉnh sửa</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function calculateTotal() {
        var weight = parseFloat($('#new_weight').val()) || 0;
        var unitPrice = parseFloat($('#new_unit_price').val()) || 0;
        var total = weight * unitPrice;
        $('#new_total').text(new Intl.NumberFormat('vi-VN').format(total) + ' {currency_symbol}');
    }

    $('#new_weight, #new_unit_price').on('input', calculateTotal);
    calculateTotal();
    
    $('#adjustmentForm').submit(function(e) {
        e.preventDefault();
        $('button[type="submit"]').prop('disabled', true);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                //
                if (response.success) {
                    alert(response.message);
                    window.location.href = '{site_url}yard-goods-delivery/history';
                } else {
                    alert(response.message);
                    $('button[type="submit"]').prop('disabled', false);
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi gửi đề nghị chỉnh sửa. Vui lòng thử lại.');
                $('button[type="submit"]').prop('disabled', false);
            }
        });
    });
});
</script>