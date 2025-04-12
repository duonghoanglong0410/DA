<div class="container-fluid mt-3">
    <!-- Phần chọn loại phiếu -->
    <div id="option-buttons" class="row justify-content-center mb-4">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chọn kiểu lập phiếu</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                    <button id="btn-receipt-by-vehicle" class="btn btn-success w-75 py-4 mb-4 fs-5 btn-lg py-3">Lập phiếu theo xe</button>
                    <button id="btn-receipt-summary" class="btn btn-info w-75 py-4 fs-5 btn-lg py-3 mb-4">Lập phiếu tổng hợp</button>
                    <button id="btn-cancel" class="btn btn-secondary w-75 py-4 fs-5 btn-lg py-3" onclick="history.back()">Quay lại</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Phần chọn loại mặt hàng -->
    <div id="product-type-selection" class="row justify-content-center mb-4" style="display: none;">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chọn loại mặt hàng</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                    {products}
                    <button class="btn-product-type btn btn-outline-primary w-75 py-3 mb-3 fs-5 btn-lg py-3" data-name="{name}" data-id="{id}">{name}</button>
                    {/products}
                </div>
                <div class="mt-3 d-flex justify-content-start">
                    <button id="btn-back-to-options" class="btn btn-secondary btn-lg py-3">Quay lại</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Danh sách phiếu cân -->
    <div id="receipt-list-section" class="row justify-content-center" style="display: none;">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Phiếu cân NK ngày {current_date} - <span id="selected-product-type">Tất cả</span></h5>
                </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Biển số xe</th>
                                    <!-- <th>Loại hàng</th> -->
                                    <th class="text-end">KL hàng (kg)</th>
                                    <th class="text-end">Đơn giá</th>
                                </tr>
                            </thead>
                            <tbody>
                                {today_can_data}
                                <tr id="{row_id}" class="selectable-row" data-loaihang="{Loaihang}" data-yard-id="{purchase_yard_id}" 
                                    data-id="{id}" data-klhang="{KLhang}" data-dongia="{Dongia}" data-soxe="{Soxe}">
                                    <td>{Soxe}</td>
                                    <!-- <td>{Loaihang}</td> -->
                                    <td class="text-end">{KLhang_formatted}</td>
                                    <td class="text-end">{Dongia_formatted}</td>
                                </tr>
                                {/today_can_data}
                                
                                <!-- Hiển thị thông báo nếu không có dữ liệu -->
                                {no_data_message}
                                <tr>
                                    <td colspan="3" class="text-center">{message}</td>
                                </tr>
                                {/no_data_message}
                                
                                <!-- Hiển thị khi không có phiếu cân phù hợp với loại hàng đã chọn -->
                                <tr id="no-matching-data" style="display: none;">
                                    <td colspan="3" class="text-center">Không có phiếu cân nào phù hợp với loại hàng đã chọn.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <div class="mt-3">
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <button id="btn-back" class="btn btn-secondary w-100 btn-lg py-3">Quay lại</button>
                        </div>
                        <div class="col-6 col-md-3">
                            <button id="btn-select-all" class="btn btn-success w-100 btn-lg py-3">Chọn tất cả</button>
                        </div>
                        <div class="col-6 col-md-3">
                            <button id="btn-refresh" class="btn btn-info w-100 btn-lg py-3">Làm mới</button>
                        </div>
                        <div class="col-6 col-md-3">
                            <button id="btn-next" class="btn btn-primary w-100 btn-lg py-3">Tiếp theo</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
            
    <!-- Form nhập thông tin phiếu -->
    <div id="receipt-form-section" style="display: none;">
        <form id="receiptForm" method="post" action="{site_url}yard-goods-receipt/save">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="form-box getDetail">
                        <div class="card-header bg-primary text-white">
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
                                <input type="number" id="quantity" name="quantity" class="form-control" step="1" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="unit_price" class="form-label">Đơn giá</label>
                                <input type="number" id="unit_price" name="unit_price" class="form-control" step="1" required>
                            </div>
                            
                            <!-- Hiển thị tổng tiền -->
                            <div class="form-group mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="form-label">Tổng tiền:</label>
                                    <span id="total_amount" class="fs-5 fw-bold">0</span>
                                </div>
                            </div>
                            
                            <!-- Danh sách ID đã chọn -->
                            <input type="hidden" id="selected_items" name="selected_items" value="">
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <button type="button" id="btn-back-to-list" class="btn btn-secondary btn-lg py-3">Quay lại</button>
                            <button type="submit" class="btn btn-primary btn-lg py-3">Lập phiếu nhập hàng</button>
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
        let filteredProductName = ''; // Lưu tên loại hàng đã lọc
        let receiptMode = ''; // 'byVehicle' or 'summary'
        // Mảng lưu trữ các dòng đã chọn
        let selectedRows = [];
        
        // Kiểm tra nếu có trạng thái lưu trong localStorage
        const savedState = localStorage.getItem('yardReceiptState');
        const savedProductName = localStorage.getItem('yardReceiptProductName');
        const savedProductId = localStorage.getItem('yardReceiptProductId');
        
        // Kiểm tra nếu chỉ có một loại mặt hàng
        const singleProductCategory = '{single_product_category}' === 'true';
        
        // Biến lưu trữ ký hiệu tiền tệ hiện tại
        let currentCurrencySymbol = 'VNĐ';
        
        if (savedState === 'receipt-list' && savedProductName) {
            // Khôi phục trạng thái trước đó
            receiptMode = 'byVehicle';
            $('#option-buttons').hide();
            $('#receipt-list-section').show();
            
            // Khôi phục tên loại hàng đã chọn
            filteredProductName = savedProductName;
            $('#selected-product-type').text(filteredProductName);
            
            // Xóa trạng thái lưu để tránh lặp lại
            localStorage.removeItem('yardReceiptState');
            localStorage.removeItem('yardReceiptProductName');
            localStorage.removeItem('yardReceiptProductId');
            
            // Kích hoạt lọc danh sách phiếu cân sau khi trang đã tải xong
            setTimeout(function() {
                filterReceiptsByProductType(filteredProductName);
                
                // Chọn giá trị category_id tương ứng nếu có
                if (savedProductId) {
                    $('#category_id').val(savedProductId);
                }
            }, 100);
        }
        
        // Dữ liệu về loại tiền tệ của các bãi
        const yardCurrencies = JSON.parse('{!yard_currencies_json!}');
        
        // Event handlers cho các nút chọn kiểu lập phiếu
        $('#btn-receipt-by-vehicle').on('click', function() {
            receiptMode = 'byVehicle';
            $('#option-buttons').hide();
            
            // Nếu chỉ có một loại mặt hàng, tự động chọn và bỏ qua bước chọn loại mặt hàng
            if (singleProductCategory) {
                // Lấy thông tin loại mặt hàng duy nhất
                const productName = '{auto_selected_category_name}';
                const productId = '{auto_selected_category_id}';
                
                // Thiết lập loại hàng đã chọn
                filteredProductName = productName;
                $('#selected-product-type').text(productName);
                
                // Hiển thị danh sách phiếu cân
                $('#receipt-list-section').show();
                
                // Lọc danh sách phiếu cân theo loại hàng
                filterReceiptsByProductType(productName);
                
                // Chọn giá trị category_id tương ứng
                $('#category_id').val(productId);
            } else {
                // Hiển thị bước chọn loại mặt hàng nếu có nhiều loại
                $('#product-type-selection').show();
            }
        });
        
        // Event handler cho nút quay lại từ màn hình chọn loại hàng
        $('#btn-back-to-options').on('click', function() {
            $('#product-type-selection').hide();
            $('#option-buttons').show();
        });
        
        // Event handler cho các nút chọn loại mặt hàng
        $('.btn-product-type').on('click', function() {
            const productName = $(this).data('name');
            const productId = $(this).data('id');
            
            // Lưu thông tin loại hàng đã chọn
            filteredProductName = productName;
            
            // Hiển thị tên loại hàng đã chọn
            $('#selected-product-type').text(productName);
            
            // Ẩn màn hình chọn loại hàng
            $('#product-type-selection').hide();
            
            // Hiển thị danh sách phiếu cân
            $('#receipt-list-section').show();
            
            // Lọc danh sách phiếu cân theo loại hàng
            filterReceiptsByProductType(productName);
            
            // Chọn giá trị category_id tương ứng
            $('#category_id').val(productId);
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
            
            // Tự động chọn giá trị đầu tiên cho các dropdown
            selectFirstOptions();
            
            // Hiển thị currency nếu yard đầu tiên có nhiều loại tiền tệ
            const firstYardId = $('#yard_id').val();
            if (firstYardId) {
                showHideCurrencySelect(firstYardId);
            }
            
            $('#vehicle_number').val('');
            $('#quantity').val('');
            $('#unit_price').val('');
            
            // Focus vào input đầu tiên
            focusFirstInput();
        });
        
        // Event handler cho nút chọn tất cả
        $('#btn-select-all').on('click', function() {
            // Chỉ chọn các dòng đang hiển thị (không bị ẩn)
            $('.selectable-row:visible').each(function() {
                const $row = $(this);
                const rowId = $row.data('id');
                const loaiHang = $row.data('loaihang');
                const klHang = parseFloat($row.data('klhang')) || 0;
                const donGia = parseFloat($row.data('dongia')) || 0;
                const soXe = $row.data('soxe');
                const yardId = $row.data('yard-id');
                
                // Kiểm tra nếu dòng chưa được chọn
                if (!$row.hasClass('selected')) {
                    // Kiểm tra nếu loại hàng phù hợp với loại đã chọn
                    // hoặc nếu chưa có loại hàng nào được chọn
                    if (selectedProductType === '' || selectedProductType === loaiHang) {
                        // Nếu chưa có loại hàng nào được chọn, thiết lập loại đầu tiên
                        if (selectedProductType === '') {
                            selectedProductType = loaiHang;
                        }
                        
                        // Thêm vào mảng selectedRows nếu chưa có
                        const existingRow = selectedRows.find(item => item.id === rowId);
                        if (!existingRow) {
                            selectedRows.push({
                                id: rowId,
                                loaiHang: loaiHang,
                                klHang: klHang,
                                donGia: donGia,
                                soXe: soXe,
                                yardId: yardId
                            });
                        }
                        
                        // Thêm lớp selected cho dòng
                        $row.addClass('selected');
                    }
                }
            });
            
            // Cập nhật trạng thái các dòng
            updateRowSelectionState();
            
            // Cập nhật thông tin form
            updateFormInfo();
            
            // Cập nhật yardId trong dropdown theo dòng đầu tiên (nếu có)
            if (selectedRows.length > 0) {
                $('#yard_id').val(selectedRows[0].yardId);
                showHideCurrencySelect(selectedRows[0].yardId);
                
                // Tìm và chọn category_id tương ứng
                $('#category_id option').each(function() {
                    //
                    if ($(this).data('name').toLowerCase() === selectedProductType.toLowerCase()) {
                        $('#category_id').val($(this).val());
                        return false; // break loop
                    }
                });
            }
        });
        
        // Event handler cho nút làm mới
        $('#btn-refresh').on('click', function() {
            // Lưu trạng thái hiện tại vào localStorage
            localStorage.setItem('yardReceiptState', 'receipt-list');
            localStorage.setItem('yardReceiptProductName', filteredProductName);
            localStorage.setItem('yardReceiptProductId', $('#category_id').val());
            
            // Reload trang
            window.location.reload();
        });
        
        // Event handlers cho các nút điều hướng
        $('#btn-back').on('click', function() {
            $('#receipt-list-section').hide();
            
            // Nếu chỉ có một loại mặt hàng, quay lại màn hình chọn kiểu lập phiếu
            // thay vì màn hình chọn loại mặt hàng
            if (singleProductCategory) {
                $('#option-buttons').show();
            } else {
                // Nếu có nhiều loại mặt hàng, quay lại màn hình chọn loại mặt hàng
                $('#product-type-selection').show();
            }
            
            resetForm();
        });
        
        $('#btn-next').on('click', function() {
            // Kiểm tra nếu có phiếu được chọn
            if (selectedRows.length === 0) {
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
            $('#category_id').next('small.form-text').remove();
            $('#category_id').after('<small class="form-text text-muted">Loại hàng được xác định từ phiếu cân đã chọn</small>');
            
            // Focus vào input đầu tiên
            focusFirstInput();
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
                
                // Cập nhật ký hiệu tiền tệ theo loại tiền tệ đã chọn
                const $selectedOption = $('#currency_id').find('option:selected');
                if ($selectedOption.length) {
                    const text = $selectedOption.text();
                    const matches = text.match(/\(([^)]+)\)/);
                    if (matches && matches[1]) {
                        currentCurrencySymbol = matches[1];
                        updateTotalAmount();
                    }
                }
            } else {
                $('#currency_container').hide();
                $('#currency_id').prop('required', false);
                
                // Nếu bãi chỉ có một loại tiền tệ, lấy ký hiệu từ dữ liệu bãi
                if (yardId && yardCurrencies[yardId] && yardCurrencies[yardId].length === 1) {
                    const currencyId = yardCurrencies[yardId][0];
                    // Tìm thông tin tiền tệ trong danh sách các tùy chọn
                    $('#currency_id option').each(function() {
                        //
                        if ($(this).val() == currencyId) {
                            const text = $(this).text();
                            const matches = text.match(/\(([^)]+)\)/);
                            if (matches && matches[1]) {
                                currentCurrencySymbol = matches[1];
                                updateTotalAmount();
                            }
                            return false; // break each loop
                        }
                    });
                }
            }
        }
        
        // Thêm CSS cho dòng có thể chọn và dòng được chọn
        $('<style>')
            .text('.selectable-row { cursor: pointer; } .selectable-row.selected { background-color: #b8e0ff !important; }')
            .appendTo('head');
        
        // Xử lý khi click vào dòng trong bảng
        $(document).on('click', '.selectable-row', function() {
            // Bỏ qua nếu dòng đang bị ẩn
            if ($(this).is(':hidden')) {
                return;
            }
            
            const $row = $(this);
            const rowId = $row.data('id');
            const loaiHang = $row.data('loaihang');
            const klHang = parseFloat($row.data('klhang')) || 0;
            const donGia = parseFloat($row.data('dongia')) || 0;
            const soXe = $row.data('soxe');
            const yardId = $row.data('yard-id');
            
            // Kiểm tra nếu dòng đã được chọn
            const isSelected = $row.hasClass('selected');
            
            if (!isSelected) {
                // Thêm dòng mới
                // Kiểm tra nếu loại hàng phù hợp
                if (selectedProductType === '' || selectedProductType === loaiHang) {
                    selectedProductType = loaiHang;
                    
                    // Thêm vào mảng selectedRows
                    selectedRows.push({
                        id: rowId,
                        loaiHang: loaiHang,
                        klHang: klHang,
                        donGia: donGia,
                        soXe: soXe,
                        yardId: yardId
                    });
                    
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
                    
                    // Thêm lớp selected cho dòng
                    $row.addClass('selected');
                } else {
                    alert(`Chỉ được chọn một loại hàng. Bạn đã chọn "${selectedProductType}".`);
                    return;
                }
            } else {
                // Bỏ chọn dòng
                // Xóa khỏi mảng selectedRows
                selectedRows = selectedRows.filter(item => item.id !== rowId);
                
                // Xóa lớp selected
                $row.removeClass('selected');
                
                // Nếu không còn dòng nào được chọn, reset selectedProductType
                if (selectedRows.length === 0) {
                    selectedProductType = '';
                }
            }
            
            // Cập nhật trạng thái các dòng khác loại
            updateRowSelectionState();
            
            // Cập nhật thông tin form
            updateFormInfo();
        });
        
        // Hàm cập nhật trạng thái khả dụng của các dòng
        function updateRowSelectionState() {
            $('.selectable-row').each(function() {
                // Bỏ qua nếu dòng đang bị ẩn do lọc
                if ($(this).is(':hidden')) {
                    return;
                }
                
                const $row = $(this);
                const loaiHang = $row.data('loaihang');
                
                // Thêm/xóa lớp mờ cho các dòng khác loại
                if (selectedProductType !== '' && loaiHang !== selectedProductType) {
                    $row.addClass('text-muted');
                } else {
                    $row.removeClass('text-muted');
                }
            });
        }
        
        // Cập nhật thông tin form dựa trên các mục đã chọn
        function updateFormInfo() {
            const selectedIds = selectedRows.map(item => item.id);
            let totalWeight = 0;
            let totalValue = 0;
            let vehicleNumber = '';
            
            selectedRows.forEach(function(item) {
                totalWeight += item.klHang;
                totalValue += item.klHang * item.donGia;
                
                // Xử lý biển số xe
                if (vehicleNumber === '') {
                    vehicleNumber = item.soXe;
                } else if (vehicleNumber !== item.soXe && vehicleNumber !== 'Tổng hợp') {
                    vehicleNumber = 'Tổng hợp';
                }
            });
            
            // Cập nhật giá trị hidden input
            $('#selected_items').val(selectedIds.join(','));
            
            // Cập nhật trường khối lượng - làm tròn thành số nguyên
            $('#quantity').val(Math.round(totalWeight));
            
            // Cập nhật trường đơn giá (trung bình) - làm tròn thành số nguyên
            let avgPrice = 0;
            // Tính giá trung bình nếu có trọng lượng
            if (totalWeight > 0) {
                avgPrice = Math.round(totalValue / totalWeight);
            }
            $('#unit_price').val(avgPrice);
            
            // Cập nhật trường biển số xe
            $('#vehicle_number').val(vehicleNumber);
            
            // Cập nhật tổng tiền
            $('#total_amount').text(formatCurrency(totalValue));
        }
        
        // Reset form và các lựa chọn
        function resetForm() {
            // Bỏ chọn tất cả các dòng
            $('.selectable-row').removeClass('selected text-muted');
            
            // Reset mảng selectedRows
            selectedRows = [];
            
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
            
            // Cập nhật tổng tiền
            $('#total_amount').text(formatCurrency(0));
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
        
        // Hàm lọc danh sách phiếu cân theo loại hàng
        function filterReceiptsByProductType(productName) {
            let hasMatchingReceipts = false;
            
            $('.selectable-row').each(function() {
                const $row = $(this);
                const loaiHang = $row.data('loaihang');
                
                // So sánh không phân biệt hoa thường
                if (loaiHang.toLowerCase() === productName.toLowerCase()) {
                    $row.show();
                    hasMatchingReceipts = true;
                } else {
                    $row.hide();
                }
            });
            
            // Hiển thị thông báo nếu không có phiếu cân phù hợp
            if (!hasMatchingReceipts) {
                $('#no-matching-data').show();
            } else {
                $('#no-matching-data').hide();
            }
        }
        
        // Khi trang tải xong, chọn giá trị đầu tiên cho các dropdown
        selectFirstOptions();
        
        // Hàm tính và cập nhật tổng tiền
        function updateTotalAmount() {
            const quantity = parseFloat($('#quantity').val()) || 0;
            const unitPrice = parseFloat($('#unit_price').val()) || 0;
            const totalAmount = quantity * unitPrice;
            
            // Hiển thị tổng tiền với định dạng số của Việt Nam
            $('#total_amount').text(formatCurrency(totalAmount));
        }
        
        // Hàm định dạng số kiểu Việt Nam
        function formatCurrency(amount) {
            return amount.toLocaleString('vi-VN') + ' ' + currentCurrencySymbol;
        }
        
        // Cập nhật ký hiệu tiền tệ khi thay đổi loại tiền tệ
        $('#currency_id').on('change', function() {
            const $selectedOption = $(this).find('option:selected');
            if ($selectedOption.length) {
                const text = $selectedOption.text();
                const matches = text.match(/\(([^)]+)\)/);
                if (matches && matches[1]) {
                    currentCurrencySymbol = matches[1];
                }
            }
            updateTotalAmount();
        });
        
        // Khởi tạo ký hiệu tiền tệ khi trang tải
        (function initDefaultCurrency() {
            const $selectedOption = $('#currency_id').find('option:selected');
            if ($selectedOption.length) {
                const text = $selectedOption.text();
                const matches = text.match(/\(([^)]+)\)/);
                if (matches && matches[1]) {
                    currentCurrencySymbol = matches[1];
                }
            }
        })();
        
        // Theo dõi sự thay đổi của khối lượng và đơn giá để cập nhật tổng tiền
        $('#quantity, #unit_price').on('input change', function() {
            updateTotalAmount();
        });
        
        // Cập nhật tổng tiền khi trang tải xong
        updateTotalAmount();
        
        // Hàm focus vào input đầu tiên không bị vô hiệu hóa trong form
        function focusFirstInput() {
            setTimeout(function() {
                // Tìm input đầu tiên không bị disabled hoặc readonly
                const $firstInput = $('#receipt-form-section').find('input:not([readonly])').first();
                
                if ($firstInput.length) {
                    $firstInput.focus();
                    
                    // Nếu là select, mở dropdown
                    if ($firstInput.is('select')) {
                        $firstInput.trigger('click');
                    }
                }
            }, 100); // Delay nhỏ để đảm bảo DOM đã được cập nhật
        }
    });
</script> 