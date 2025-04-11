<div class="container-fluid mt-3">
    <h2 class="mb-4 text-center">Lập phiếu cân nhập hàng</h2>
    
    <!-- Phần chọn loại phiếu -->
    <div id="option-buttons" class="row justify-content-center mb-4">
        <div class="col-md-6 text-center">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chọn kiểu lập phiếu</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                    <button id="btn-receipt-by-vehicle" class="btn btn-success w-75 py-4 mb-4 fs-5">Lập phiếu theo xe</button>
                    <button id="btn-receipt-summary" class="btn btn-info w-75 py-4 fs-5">Lập phiếu tổng hợp</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Danh sách phiếu cân -->
    <div id="receipt-list-section" class="row" style="display: none;">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Danh sách phiếu cân NK ngày {current_date}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th width="50px">Chọn</th>
                                    <th>Biển số xe</th>
                                    <th>Loại hàng</th>
                                    <th class="text-end">KL hàng</th>
                                    <th class="text-end">Đơn giá</th>
                                </tr>
                            </thead>
                            <tbody>
                                {today_can_data}
                                <tr id="{row_id}" data-loaihang="{Loaihang}" data-yard-id="{purchase_yard_id}">
                                    <td class="text-center">
                                        <input type="checkbox" class="select-item" data-id="{id}" data-loaihang="{Loaihang}" 
                                               data-klhang="{KLhang}" data-dongia="{Dongia}" data-soxe="{Soxe}" 
                                               data-yard-id="{purchase_yard_id}">
                                    </td>
                                    <td>{Soxe}</td>
                                    <td>{Loaihang}</td>
                                    <td class="text-end">{KLhang_formatted}</td>
                                    <td class="text-end">{Dongia_formatted}</td>
                                </tr>
                                {/today_can_data}
                                
                                <!-- Hiển thị thông báo nếu không có dữ liệu -->
                                {no_data_message}
                                <tr>
                                    <td colspan="5" class="text-center">{message}</td>
                                </tr>
                                {/no_data_message}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <button id="btn-back" class="btn btn-secondary">Quay lại</button>
                        <button id="btn-next" class="btn btn-primary">Tiếp theo</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
            
    <!-- Form nhập thông tin phiếu -->
    <div id="receipt-form-section" style="display: none;">
        <form id="receiptForm" method="post" action="{site_url}yard-goods-receipt/save">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Thông tin phiếu nhập hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="yard_id" class="form-label">Bãi nhập hàng</label>
                                <select id="yard_id" name="yard_id" class="form-select" required>
                                    {authorized_yards}
                                    <option value="{purchase_yard_id}">{yard_name} ({yard_code})</option>
                                    {/authorized_yards}
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="category_id" class="form-label">Loại mặt hàng</label>
                                <select id="category_id" name="category_id" class="form-select" required>
                                    {products}
                                    <option value="{id}" data-name="{name}">{name}</option>
                                    {/products}
                                </select>
                            </div>
                            
                            <div class="form-group mb-3" id="currency_container" style="display: none;">
                                <label for="currency_id" class="form-label">Loại tiền tệ</label>
                                <select id="currency_id" name="currency_id" class="form-select">
                                    {currencies}
                                    <option value="{id}">{name} ({symbol})</option>
                                    {/currencies}
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="vehicle_number" class="form-label">Biển số xe</label>
                                <input type="text" id="vehicle_number" name="vehicle_number" class="form-control" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="quantity" class="form-label">Khối lượng</label>
                                <input type="number" id="quantity" name="quantity" class="form-control" step="0.01" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="unit_price" class="form-label">Đơn giá</label>
                                <input type="number" id="unit_price" name="unit_price" class="form-control" step="0.01" required>
                            </div>
                            
                            <!-- Danh sách ID đã chọn -->
                            <input type="hidden" id="selected_items" name="selected_items" value="">
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <button type="button" id="btn-back-to-list" class="btn btn-secondary">Quay lại</button>
                                <button type="submit" class="btn btn-primary">Lập phiếu nhập hàng</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Biến lưu trữ thông tin loại hàng đã chọn
        let selectedProductType = '';
        let receiptMode = ''; // 'byVehicle' or 'summary'
        
        // Dữ liệu về loại tiền tệ của các bãi
        const yardCurrencies = {yard_currencies_json};
        
        // Event handlers cho các nút chọn kiểu lập phiếu
        $('#btn-receipt-by-vehicle').on('click', function() {
            receiptMode = 'byVehicle';
            $('#option-buttons').hide();
            $('#receipt-list-section').show();
        });
        
        $('#btn-receipt-summary').on('click', function() {
            receiptMode = 'summary';
            $('#option-buttons').hide();
            
            // Chuyển thẳng tới màn hình nhập form
            $('#receipt-form-section').show();
            
            // Đảm bảo category_id có thể chọn được
            $('#category_id').prop('disabled', false);
            $('#category_id').parent('.form-group').removeClass('opacity-75');
            $('#category_id').next('small').remove();
            
            // Reset form data
            $('#selected_items').val('');
            
            // Tự động chọn giá trị đầu tiên cho mỗi dropdown
            selectFirstOptions();
            
            // Hiển thị currency nếu yard đầu tiên có nhiều loại tiền tệ
            const firstYardId = $('#yard_id').val();
            if (firstYardId) {
                showHideCurrencySelect(firstYardId);
            }
            
            $('#vehicle_number').val('');
            $('#quantity').val('');
            $('#unit_price').val('');
        });
        
        // Event handlers cho các nút điều hướng
        $('#btn-back').on('click', function() {
            $('#receipt-list-section').hide();
            $('#option-buttons').show();
            resetForm();
        });
        
        $('#btn-next').on('click', function() {
            // Kiểm tra nếu có phiếu được chọn
            if ($('.select-item:checked').length === 0) {
                alert('Vui lòng chọn ít nhất một phiếu cân!');
                return;
            }
            
            // Vô hiệu hóa combobox category_id để không thể thay đổi
            $('#category_id').prop('disabled', false); // Tạm thời bật lại để đảm bảo giá trị được submit
            
            $('#receipt-list-section').hide();
            $('#receipt-form-section').show();
            
            // Vô hiệu hóa lại category dropdown sau khi hiển thị form
            $('#category_id').prop('disabled', true);
            $('#category_id').parent('.form-group').addClass('opacity-75');
            $('#category_id').after('<small class="form-text text-muted">Loại hàng được xác định từ phiếu cân đã chọn</small>');
        });
        
        $('#btn-back-to-list').on('click', function() {
            // Kiểm tra chế độ hiện tại
            if (receiptMode === 'byVehicle') {
                // Quay lại danh sách phiếu nếu đang ở chế độ theo xe
                $('#receipt-form-section').hide();
                $('#receipt-list-section').show();
                
                // Bật lại combobox category_id khi quay lại danh sách
                $('#category_id').prop('disabled', false);
                $('#category_id').parent('.form-group').removeClass('opacity-75');
                $('#category_id').next('small').remove();
            } else {
                // Quay lại màn hình chọn chế độ nếu đang ở chế độ tổng hợp
                $('#receipt-form-section').hide();
                $('#option-buttons').show();
                resetForm();
            }
        });
        
        // Xử lý khi chọn bãi
        $('#yard_id').on('change', function() {
            const yardId = $(this).val();
            showHideCurrencySelect(yardId);
        });
        
        // Hàm hiển thị/ẩn dropdown chọn loại tiền tệ
        function showHideCurrencySelect(yardId) {
            // Kiểm tra nếu bãi có nhiều loại tiền tệ
            if (yardId && yardCurrencies[yardId] && yardCurrencies[yardId].length > 1) {
                $('#currency_container').show();
                $('#currency_id').prop('required', true);
            } else {
                $('#currency_container').hide();
                $('#currency_id').prop('required', false);
            }
        }
        
        // Xử lý khi click vào checkbox hoặc dòng trong bảng
        $('.select-item').each(function() {
            const $checkbox = $(this);
            
            $checkbox.on('change', function() {
                handleItemSelection($(this));
            });
            
            // Thêm sự kiện click cho cả dòng
            $checkbox.closest('tr').on('click', function(e) {
                // Chỉ xử lý nếu không click vào chính checkbox
                if (e.target !== $checkbox.get(0)) {
                    $checkbox.prop('checked', !$checkbox.prop('checked'));
                    handleItemSelection($checkbox);
                }
            });
        });
        
        // Xử lý khi chọn/bỏ chọn một mục
        function handleItemSelection($checkbox) {
            const loaiHang = $checkbox.data('loaihang');
            const klHang = parseFloat($checkbox.data('klhang')) || 0;
            const donGia = parseFloat($checkbox.data('dongia')) || 0;
            const soXe = $checkbox.data('soxe');
            const yardId = $checkbox.data('yard-id');
            
            // Nếu đang chọn một checkbox
            if ($checkbox.prop('checked')) {
                // Nếu chưa có loại hàng nào được chọn hoặc loại hàng trùng với loại đã chọn
                if (selectedProductType === '' || selectedProductType === loaiHang) {
                    selectedProductType = loaiHang;
                    
                    // Cập nhật yardId trong dropdown
                    $('#yard_id').val(yardId);
                    showHideCurrencySelect(yardId);
                    
                    // Tìm và chọn category_id tương ứng
                    $('#category_id option').each(function() {
                        // Kiểm tra nếu tên sản phẩm trùng với loại hàng (không phân biệt hoa thường)
                        if ($(this).data('name').toLowerCase() === loaiHang.toLowerCase()) {
                            $('#category_id').val($(this).val());
                            return false; // break loop
                        }
                    });
                } else {
                    // Nếu loại hàng không trùng khớp, bỏ chọn checkbox này
                    $checkbox.prop('checked', false);
                    alert(`Chỉ được chọn một loại hàng. Bạn đã chọn "${selectedProductType}".`);
                    return;
                }
            } else {
                // Nếu bỏ chọn tất cả, reset selectedProductType
                if ($('.select-item:checked').length === 0) {
                    selectedProductType = '';
                }
            }
            
            // Khóa/mở các checkbox khác loại
            $('.select-item').each(function() {
                const itemLoaiHang = $(this).data('loaihang');
                // Kiểm tra nếu đã chọn loại hàng khác
                if (selectedProductType !== '' && itemLoaiHang !== selectedProductType) {
                    $(this).prop('disabled', true);
                    $(this).closest('tr').addClass('text-muted');
                } else {
                    $(this).prop('disabled', false);
                    $(this).closest('tr').removeClass('text-muted');
                }
            });
            
            // Cập nhật thông tin form
            updateFormInfo();
        }
        
        // Cập nhật thông tin form dựa trên các mục đã chọn
        function updateFormInfo() {
            const selectedIds = [];
            let totalWeight = 0;
            let totalValue = 0;
            let vehicleNumber = '';
            
            $('.select-item:checked').each(function() {
                const id = $(this).data('id');
                const weight = parseFloat($(this).data('klhang')) || 0;
                const price = parseFloat($(this).data('dongia')) || 0;
                const soXe = $(this).data('soxe');
                
                selectedIds.push(id);
                totalWeight += weight;
                totalValue += weight * price;
                
                // Xử lý biển số xe
                if (vehicleNumber === '') {
                    vehicleNumber = soXe;
                } else if (vehicleNumber !== soXe && vehicleNumber !== 'Tổng hợp') {
                    vehicleNumber = 'Tổng hợp';
                }
            });
            
            // Cập nhật giá trị hidden input
            $('#selected_items').val(selectedIds.join(','));
            
            // Cập nhật trường khối lượng
            $('#quantity').val(totalWeight.toFixed(2));
            
            // Cập nhật trường đơn giá (trung bình)
            let avgPrice = 0;
            // Tính giá trung bình nếu có trọng lượng
            if (totalWeight > 0) {
                avgPrice = totalValue / totalWeight;
            }
            $('#unit_price').val(avgPrice.toFixed(2));
            
            // Cập nhật trường biển số xe
            $('#vehicle_number').val(vehicleNumber);
        }
        
        // Reset form và các lựa chọn
        function resetForm() {
            // Bỏ chọn tất cả các checkbox
            $('.select-item').each(function() {
                $(this).prop('checked', false);
                $(this).prop('disabled', false);
                $(this).closest('tr').removeClass('text-muted');
            });
            
            // Reset selectedProductType
            selectedProductType = '';
            
            // Bật lại combobox category_id
            $('#category_id').prop('disabled', false);
            $('#category_id').parent('.form-group').removeClass('opacity-75');
            $('#category_id').next('small').remove();
            
            // Reset lại form
            $('#selected_items').val('');
            selectFirstOptions();
            $('#vehicle_number').val('');
            $('#quantity').val('');
            $('#unit_price').val('');
            
            // Ẩn dropdown currency nếu đang hiển thị
            $('#currency_container').hide();
        }
        
        // Xử lý form submit
        $('#receiptForm').on('submit', function(e) {
            e.preventDefault();
            
            // Tạm thời bật lại disabled elements để đảm bảo tất cả các trường được gửi lên server
            $('#category_id').prop('disabled', false);
            
            // Kiểm tra form trước khi submit
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                // Vô hiệu hóa lại category dropdown
                $('#category_id').prop('disabled', true);
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
                        // Reload trang sau khi lưu thành công
                        window.location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Đã xảy ra lỗi khi xử lý yêu cầu.');
                }
            });
            
            // Vô hiệu hóa lại category dropdown sau khi submit
            $('#category_id').prop('disabled', true);
        });
        
        // Hàm chọn giá trị đầu tiên cho các dropdown
        function selectFirstOptions() {
            // Chọn bãi đầu tiên nếu có
            if ($('#yard_id option').length > 0) {
                $('#yard_id').val($('#yard_id option:first').val());
            }
            
            // Chọn loại hàng đầu tiên nếu có
            if ($('#category_id option').length > 0) {
                $('#category_id').val($('#category_id option:first').val());
            }
            
            // Chọn tiền tệ đầu tiên nếu có
            if ($('#currency_id option').length > 0) {
                $('#currency_id').val($('#currency_id option:first').val());
            }
        }
        
        // Khi trang tải xong, chọn giá trị đầu tiên cho các dropdown
        selectFirstOptions();
    });
</script> 