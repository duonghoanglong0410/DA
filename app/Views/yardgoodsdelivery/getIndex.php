<div class="container-fluid mt-3">
    <!-- Phần chọn loại mặt hàng -->
    <div id="product-type-selection" class="row justify-content-center mb-4">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Lập phiếu cân xuất hàng</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                    {products}
                    <button class="btn-product-type btn btn-outline-primary w-75 py-3 mb-3 fs-5 btn-lg py-3" data-name="{name}" data-id="{id}">{name}</button>
                    {/products}
                </div>
                <div class="mt-3 d-flex justify-content-start">
                    <button id="btn-back-to-home" class="btn btn-secondary btn-lg py-3" onclick="history.back()">Quay lại</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Phần chọn loại xuất hàng -->
    <div id="delivery-type-selection" class="row justify-content-center mb-4" style="display: none;">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chọn loại xuất hàng - <span id="selected-product-name"></span></h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                    <button id="btn-internal-delivery" class="btn btn-warning w-75 py-3 mb-4 fs-5 btn-lg py-3">Xuất nội bộ</button>
                    <button id="btn-direct-sale" class="btn btn-success w-75 py-3 mb-4 fs-5 btn-lg py-3">Bán trực tiếp</button>
                </div>
                <div class="mt-3 d-flex justify-content-start">
                    <button id="btn-back-to-product" class="btn btn-secondary btn-lg py-3">Quay lại</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Phần chọn phương thức xuất nội bộ -->
    <div id="internal-delivery-method" class="row justify-content-center mb-4" style="display: none;">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Chọn phương thức xuất nội bộ - <span id="internal-product-name"></span></h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                    <button id="btn-select-scale" class="btn btn-info w-75 py-3 mb-4 fs-5 btn-lg py-3">Chọn phiếu cân</button>
                    <button id="btn-select-truck" class="btn btn-primary w-75 py-3 mb-4 fs-5 btn-lg py-3">Chọn xe tải</button>
                    <button id="btn-new-truck" class="btn btn-success w-75 py-3 fs-5 btn-lg py-3">Xe tải mới</button>
                </div>
                <div class="mt-3 d-flex justify-content-start">
                    <button id="btn-back-to-delivery-type" class="btn btn-secondary btn-lg py-3">Quay lại</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Danh sách phiếu cân XK -->
    <div id="scale-list-section" class="row justify-content-center" style="display: none;">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Phiếu cân XK ngày {current_date} - <span id="scale-product-name"></span></h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Biển số xe</th>
                                <th class="text-end">KL hàng (kg)</th>
                                <th class="text-end">Đơn giá</th>
                            </tr>
                        </thead>
                        <tbody>
                            {xk_can_data}
                            <tr id="{row_id}" class="selectable-row" data-loaihang="{Loaihang}" data-yard-id="{purchase_yard_id}" 
                                data-id="{id}" data-klhang="{KLhang}" data-dongia="{Dongia}" data-soxe="{Soxe}">
                                <td>{Soxe}</td>
                                <td class="text-end">{KLhang_formatted}</td>
                                <td class="text-end">{Dongia_formatted}</td>
                            </tr>
                            {/xk_can_data}
                            
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
                        <div class="col-6 col-md-4">
                            <button id="btn-back-to-internal" class="btn btn-secondary w-100 btn-lg py-3">Quay lại</button>
                        </div>
                        <div class="col-6 col-md-4">
                            <button id="btn-scale-refresh" class="btn btn-info w-100 btn-lg py-3">Làm mới</button>
                        </div>
                        <div class="col-12 col-md-4">
                            <button id="btn-scale-next" class="btn btn-primary w-100 btn-lg py-3">Tiếp theo</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Danh sách xe tải đang vận chuyển -->
    <div id="truck-list-section" class="row justify-content-center mb-4" style="display: none;">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Danh sách xe tải đang vận chuyển</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                    {trucks}
                    <button class="btn-truck btn btn-outline-primary w-75 py-3 mb-3 fs-5 btn-lg py-3" 
                            data-id="{id}" data-vehicle="{vehicle_number}" data-weight="{total_export_weight}" 
                            data-category="{product_category_id}">
                        {vehicle_number} ({total_export_weight_formatted} kg)
                    </button>
                    {/trucks}
                    
                    <!-- Hiển thị khi không có xe tải nào đang vận chuyển -->
                    {no_trucks_message}
                    <div class="alert alert-info w-100">
                        <p class="text-center">Không có xe tải nào đang vận chuyển.</p>
                    </div>
                    {/no_trucks_message}
                </div>
                <div class="mt-3 d-flex justify-content-between">
                    <button id="btn-back-to-internal-from-truck" class="btn btn-secondary btn-lg py-3">Quay lại</button>
                    <button id="btn-truck-refresh" class="btn btn-info btn-lg py-3">Làm mới</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form lập phiếu cân xuất -->
    <div id="delivery-form-section" style="display: none;">
        <form id="deliveryForm" method="post" action="{site_url}yard-goods-delivery/save">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="form-box getDetail">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Lập phiếu cân xuất - <span id="delivery-type-display"></span></h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="yard_id" class="form-label">Bãi xuất hàng</label>
                                <select id="yard_id" name="yard_id" class="form-select" required>
                                    {authorized_yards}
                                    <option value="{purchase_yard_id}">{yard_name} ({yard_code})</option>
                                    {/authorized_yards}
                                </select>
                            </div>
                            
                            <div class="form-group mb-3" id="delivery_type_container" style="display: none;">
                                <label for="delivery_type" class="form-label">Loại xuất hàng</label>
                                <select id="delivery_type" name="delivery_type" class="form-select" required>
                                    <option value="internal">Xuất nội bộ</option>
                                    <option value="sale">Bán trực tiếp</option>
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
                            
                            <!-- Hiển thị thông tin kho -->
                            <div id="product-info-container" class="alert alert-info mb-3" style="display: none;">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Tồn kho:</strong> <span id="product-stock-weight">0</span> kg
                                    </div>
                                    <div class="col-6">
                                        <strong>Đơn giá BQ:</strong> <span id="product-average-price">0</span>
                                    </div>
                                </div>
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
                            
                            <input type="hidden" id="selected_method" name="selected_method" value="">
                            <input type="hidden" id="selected_items" name="selected_items" value="">
                            <input type="hidden" id="selected_truck" name="selected_truck" value="">
                        </div>
                        <div class="mt-3 d-flex justify-content-between">
                            <button type="button" id="btn-back-to-prev" class="btn btn-secondary btn-lg py-3">Quay lại</button>
                            <button type="submit" class="btn btn-primary btn-lg py-3">Lập phiếu xuất hàng</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal cho xe tải hiện có cùng biển số -->
<div class="modal fade" id="existingTruckModal" tabindex="-1" aria-labelledby="existingTruckModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="existingTruckModalLabel">Phát hiện xe đang vận chuyển</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Đã tìm thấy xe với biển số này đang vận chuyển. Bạn muốn thực hiện thao tác nào?</p>
                <div id="existingTruckOptions" class="mt-3">
                    <!-- Các xe hiện có sẽ được thêm ở đây -->
                </div>
                <div class="mt-3">
                    <button id="btnCreateNewTruck" class="btn btn-success w-100 py-3 mt-3">Xe tải mới</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận khi chọn phiếu cân có xe đang di chuyển -->
<div class="modal fade" id="scaleMatchTruckModal" tabindex="-1" aria-labelledby="scaleMatchTruckModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="scaleMatchTruckModalLabel">Phát hiện biển số xe trùng khớp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Phiếu cân đã chọn có biển số xe trùng với xe đang vận chuyển. Bạn muốn thực hiện thao tác nào?</p>
                <div id="scaleMatchTruckOptions" class="mt-3">
                    <!-- Các xe hiện có sẽ được thêm ở đây -->
                </div>
                <div class="mt-3">
                    <button id="btnCreateNewTruckFromScale" class="btn btn-success w-100 py-3 mt-3">Tạo xe mới</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Biến lưu trữ thông tin loại hàng đã chọn
        let selectedProductType = '';
        let selectedProductId = '';
        let selectedDeliveryType = ''; // 'internal' or 'direct'
        let selectedMethod = ''; // 'scale', 'truck', or 'new'
        let selectedRows = []; // Mảng lưu trữ các dòng đã chọn
        let selectedTruck = null; // Thông tin xe tải đã chọn
        let existingTruckId = null; // ID của xe tải hiện có khi chọn sử dụng xe cũ
        
        // Biến lưu trữ ký hiệu tiền tệ hiện tại
        let currentCurrencySymbol = 'VNĐ';
        
        // Kiểm tra nếu chỉ có một loại mặt hàng
        const singleProductCategory = '{single_product_category}' === 'true';
        
        // Khôi phục trạng thái nếu có
        function restoreState() {
            const savedState = localStorage.getItem('yardDeliveryState');
            const savedProductName = localStorage.getItem('yardDeliveryProductName');
            const savedProductId = localStorage.getItem('yardDeliveryProductId');
            const savedSelectedRows = localStorage.getItem('yardDeliverySelectedRows');
            const savedDeliveryType = localStorage.getItem('yardDeliveryType');
            
            if (savedState && savedProductName && savedProductId) {
                // Khôi phục thông tin loại mặt hàng
                selectedProductType = savedProductName;
                selectedProductId = savedProductId;
                
                // Đánh dấu loại mặt hàng đã chọn
                $('#selected-product-name').text(savedProductName);
                $('#internal-product-name').text(savedProductName);
                $('#scale-product-name').text(savedProductName);
                
                // Cài đặt loại delivery nếu có
                if (savedDeliveryType) {
                    selectedDeliveryType = savedDeliveryType;
                    $('#delivery_type').val(savedDeliveryType);
                    
                    if (savedDeliveryType === 'internal') {
                        $('#delivery-type-display').text('Xuất nội bộ');
                    } else if (savedDeliveryType === 'sale') {
                        $('#delivery-type-display').text('Bán trực tiếp');
                    }
                }
                
                // Ẩn màn hình chọn loại mặt hàng
                $('#product-type-selection').hide();
                
                // Chọn giá trị category_id tương ứng
                $('#category_id').val(savedProductId);
                
                // Khôi phục trạng thái theo loại
                if (savedState === 'scale-list') {
                    // Ẩn màn hình chọn loại xuất hàng
                    $('#delivery-type-selection').hide();
                    
                    // Ẩn màn hình chọn phương thức xuất nội bộ
                    $('#internal-delivery-method').hide();
                    
                    // Hiển thị danh sách phiếu cân
                    $('#scale-list-section').show();
                    
                    // Đánh dấu selected method
                    selectedMethod = 'scale';
                    $('#selected_method').val('scale');
                    
                    // Lọc danh sách phiếu cân
                    setTimeout(function() {
                        filterScalesByProductType(savedProductName);
                        
                        // Khôi phục dòng đã chọn
                        const savedRows = JSON.parse(localStorage.getItem('yardDeliverySelectedRows') || '[]');
                        savedRows.forEach(function(rowData) {
                            const $row = $(`#${rowData.id}`);
                            if ($row.length) {
                                $row.addClass('selected');
                                selectedRows.push(rowData);
                            }
                        });
                    }, 100);
                    
                    // Xóa trạng thái lưu để tránh lặp lại
                    clearSavedState();
                    
                } else if (savedState === 'truck-list') {
                    // Ẩn màn hình chọn loại xuất hàng
                    $('#delivery-type-selection').hide();
                    
                    // Ẩn màn hình chọn phương thức xuất nội bộ
                    $('#internal-delivery-method').hide();
                    
                    // Hiển thị danh sách xe tải
                    $('#truck-list-section').show();
                    
                    // Đánh dấu selected method
                    selectedMethod = 'truck';
                    $('#selected_method').val('truck');
                    
                    // Lọc danh sách xe tải
                    setTimeout(function() {
                        filterTrucksByCategory(savedProductId);
                    }, 100);
                    
                    // Xóa trạng thái lưu để tránh lặp lại
                    clearSavedState();
                }
            }
        }
        
        // Hàm xóa trạng thái đã lưu
        function clearSavedState() {
            localStorage.removeItem('yardDeliveryState');
            localStorage.removeItem('yardDeliveryProductName');
            localStorage.removeItem('yardDeliveryProductId');
            localStorage.removeItem('yardDeliveryType');
            localStorage.removeItem('yardDeliverySelectedRows');
        }
        
        // Nếu chỉ có một loại mặt hàng, tự động chọn và bỏ qua bước chọn loại mặt hàng
        if (singleProductCategory) {
            // Lấy thông tin loại mặt hàng duy nhất
            const productName = '{auto_selected_category_name}';
            const productId = '{auto_selected_category_id}';
            
            // Thiết lập thông tin loại hàng đã chọn
            selectedProductType = productName;
            selectedProductId = productId;
            
            // Ẩn bước chọn loại mặt hàng
            $('#product-type-selection').hide();
            
            // Hiển thị tên loại hàng
            $('#selected-product-name').text(productName);
            $('#internal-product-name').text(productName);
            $('#scale-product-name').text(productName);
            
            // Hiển thị bước chọn loại xuất hàng
            $('#delivery-type-selection').show();
            
            // Chọn giá trị category_id tương ứng
            $('#category_id').val(productId);
            
            // Lọc sẵn danh sách xe tải (cho dù không hiển thị ngay)
            filterTrucksByCategory(productId);
        }
        
        // Khôi phục trạng thái nếu có
        restoreState();
        
        // Dữ liệu về loại tiền tệ của các bãi
        const yardCurrencies = JSON.parse('{!yard_currencies_json!}');
        
        // Dữ liệu về xe tải theo từng loại mặt hàng
        const trucksByCategory = JSON.parse('{!trucks_by_category_json!}');
        
        // Event handler cho các nút chọn loại mặt hàng
        $('.btn-product-type').on('click', function() {
            const productName = $(this).data('name');
            const productId = $(this).data('id');
            
            // Lưu thông tin loại hàng đã chọn
            selectedProductType = productName;
            selectedProductId = productId;
            
            // Hiển thị tên loại hàng
            $('#selected-product-name').text(productName);
            $('#internal-product-name').text(productName);
            $('#scale-product-name').text(productName);
            
            // Ẩn bước chọn loại mặt hàng
            $('#product-type-selection').hide();
            
            // Hiển thị bước chọn loại xuất hàng
            $('#delivery-type-selection').show();
            
            // Chọn giá trị category_id tương ứng
            $('#category_id').val(productId);
        });
        
        // Kiểm tra biển số xe khi thay đổi
        $('#vehicle_number').on('change', function() {
            const vehicleNumber = $(this).val().trim();
            
            // Kiểm tra trước khi gửi AJAX
            if (selectedMethod === 'scale' && vehicleNumber) {
                // Gọi API kiểm tra
                $.ajax({
                    url: '{site_url}yard-goods-delivery/check-vehicle',
                    type: 'POST',
                    data: { vehicle_number: vehicleNumber },
                    dataType: 'json',
                    success: function(response) {
                        // Kiểm tra phương thức
                        if (response.success && response.exists) {
                            // Hiển thị modal với danh sách xe tải hiện có
                            showExistingTruckOptions(response.trips);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Xử lý lỗi
                        console.error('Error checking vehicle:', error);
                    }
                });
            }
        });
        
        // Hiển thị modal với danh sách xe tải hiện có
        function showExistingTruckOptions(trips) {
            // Xóa tất cả các tùy chọn cũ
            $('#existingTruckOptions').empty();
            
            // Thêm tùy chọn mới
            trips.forEach(function(trip) {
                const $option = $(`
                    <button class="btn btn-primary w-100 py-3 mb-2 select-existing-truck" 
                            data-id="${trip.id}" 
                            data-trip-code="${trip.trip_code}">
                        Xếp vào xe ${trip.vehicle_number} (${trip.total_export_weight_formatted} kg)
                    </button>
                `);
                $('#existingTruckOptions').append($option);
            });
            
            // Hiển thị modal
            $('#existingTruckModal').modal('show');
        }
        
        // Xử lý khi người dùng chọn xe tải hiện có
        $(document).on('click', '.select-existing-truck', function() {
            const tripId = $(this).data('id');
            
            // Lưu ID xe tải đã chọn
            existingTruckId = tripId;
            
            // Thêm hidden input cho existing_trip_id
            if ($('#existing_trip_id').length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    id: 'existing_trip_id',
                    name: 'existing_trip_id',
                    value: tripId
                }).appendTo('#deliveryForm');
            } else {
                $('#existing_trip_id').val(tripId);
            }
            
            // Đóng modal
            $('#existingTruckModal').modal('hide');
        });
        
        // Xử lý khi người dùng chọn tạo xe tải mới
        $('#btnCreateNewTruck').on('click', function() {
            // Xóa existing_trip_id nếu có
            $('#existing_trip_id').remove();
            
            // Đóng modal
            $('#existingTruckModal').modal('hide');
        });
        
        // Event handler cho nút "Xuất nội bộ"
        $('#btn-internal-delivery').on('click', function() {
            selectedDeliveryType = 'internal';
            $('#delivery_type').val('internal');
            $('#delivery-type-display').text('Xuất nội bộ');
            
            // Ẩn bước chọn loại xuất hàng
            $('#delivery-type-selection').hide();
            
            // Hiển thị bước chọn phương thức xuất nội bộ
            $('#internal-delivery-method').show();
        });
        
        // Hàm focus vào input đầu tiên không bị vô hiệu hóa trong form
        function focusFirstInput() {
            setTimeout(function() {
                // Tìm input đầu tiên không bị disabled hoặc readonly
                const $firstInput = $('#delivery-form-section').find('input:not([readonly])').first();
                
                if ($firstInput.length) {
                    $firstInput.focus();
                    
                    // Nếu là select, mở dropdown
                    if ($firstInput.is('select')) {
                        $firstInput.trigger('click');
                    }
                }
            }, 100); // Delay nhỏ để đảm bảo DOM đã được cập nhật
        }
        
        // Event handler cho nút "Bán trực tiếp"
        $('#btn-direct-sale').on('click', function() {
            selectedDeliveryType = 'sale';
            $('#delivery_type').val('sale');
            $('#delivery-type-display').text('Bán trực tiếp');
            selectedMethod = 'direct';
            $('#selected_method').val('direct');
            
            // Ẩn bước chọn loại xuất hàng
            $('#delivery-type-selection').hide();
            
            // Hiển thị form lập phiếu xuất hàng
            $('#delivery-form-section').show();
            
            // Xóa dữ liệu cũ và đặt lại form
            resetFormData();
            
            // Lấy dữ liệu từ purchase_yard_product_info
            updateProductStockInfo();
            
            // Mở khóa trường loại mặt hàng cho "Bán trực tiếp"
            $('#category_id').prop('disabled', false);
            $('#category_id').parent('.form-group').removeClass('opacity-75');
            $('#category_id').next('small.form-text').remove();
            
            // Kiểm tra và khóa trường biển số xe nếu cần
            lockVehicleNumberField();
            
            // Focus vào input đầu tiên
            focusFirstInput();
        });
        
        // Event handler cho nút "Chọn phiếu cân"
        $('#btn-select-scale').on('click', function() {
            selectedMethod = 'scale';
            $('#selected_method').val('scale');
            
            // Ẩn bước chọn phương thức xuất nội bộ
            $('#internal-delivery-method').hide();
            
            // Hiển thị danh sách phiếu cân
            $('#scale-list-section').show();
            
            // Lọc danh sách phiếu cân theo loại hàng
            filterScalesByProductType(selectedProductType);
        });
        
        // Event handler cho nút "Chọn xe tải"
        $('#btn-select-truck').on('click', function() {
            selectedMethod = 'truck';
            $('#selected_method').val('truck');
            
            // Ẩn bước chọn phương thức xuất nội bộ
            $('#internal-delivery-method').hide();
            
            // Hiển thị danh sách xe tải
            $('#truck-list-section').show();
            
            // Lọc danh sách xe tải theo loại mặt hàng đã chọn
            filterTrucksByCategory(selectedProductId);
        });
        
        // Event handler cho nút "Xe tải mới"
        $('#btn-new-truck').on('click', function() {
            selectedMethod = 'new';
            $('#selected_method').val('new');
            
            // Ẩn bước chọn phương thức xuất nội bộ
            $('#internal-delivery-method').hide();
            
            // Hiển thị form lập phiếu xuất hàng
            $('#delivery-form-section').show();
            
            // Xóa dữ liệu cũ và đặt lại form
            resetFormData();
            
            // Lấy dữ liệu từ purchase_yard_product_info
            updateProductStockInfo();
            
            // Khóa trường loại mặt hàng
            lockCategoryField();
            
            // Kiểm tra và khóa trường biển số xe nếu cần
            lockVehicleNumberField();
            
            // Focus vào input đầu tiên
            focusFirstInput();
        });
        
        // Event handler cho các nút xe tải
        $('.btn-truck').on('click', function() {
            const truckId = $(this).data('id');
            const vehicleNumber = $(this).data('vehicle');
            const weight = $(this).data('weight');
            
            // Lưu thông tin xe tải đã chọn
            selectedTruck = {
                id: truckId,
                vehicleNumber: vehicleNumber,
                weight: weight
            };
            
            $('#selected_truck').val(truckId);
            
            // Ẩn danh sách xe tải
            $('#truck-list-section').hide();
            
            // Hiển thị form lập phiếu xuất hàng
            $('#delivery-form-section').show();
            
            // Cập nhật biển số xe
            $('#vehicle_number').val(vehicleNumber);
            
            // Lấy dữ liệu từ purchase_yard_product_info
            updateProductStockInfo();
            
            // Khóa trường loại mặt hàng
            lockCategoryField();
            
            // Khóa trường biển số xe vì đã có dữ liệu
            lockVehicleNumberField();
            
            // Focus vào input đầu tiên
            focusFirstInput();
        });
        
        // Event handlers cho các nút quay lại
        $('#btn-back-to-product').on('click', function() {
            $('#delivery-type-selection').hide();
            $('#product-type-selection').show();
        });
        
        $('#btn-back-to-delivery-type').on('click', function() {
            $('#internal-delivery-method').hide();
            $('#delivery-type-selection').show();
        });
        
        $('#btn-back-to-internal').on('click', function() {
            $('#scale-list-section').hide();
            $('#internal-delivery-method').show();
            resetSelectedRows();
        });
        
        $('#btn-back-to-internal-from-truck').on('click', function() {
            $('#truck-list-section').hide();
            $('#internal-delivery-method').show();
            selectedTruck = null;
        });
        
        $('#btn-back-to-prev').on('click', function() {
            $('#delivery-form-section').hide();
            
            if (selectedMethod === 'scale') {
                // Quay lại danh sách phiếu cân, giữ nguyên các hàng đã chọn
                $('#scale-list-section').show();
            } else if (selectedMethod === 'truck') {
                // Quay lại danh sách xe tải, xóa dữ liệu đã chọn
                selectedTruck = null;
                $('#selected_truck').val('');
                $('#truck-list-section').show();
            } else if (selectedMethod === 'new') {
                // Quay lại menu chọn phương thức, xóa dữ liệu đã nhập
                resetFormData();
                $('#internal-delivery-method').show();
            } else if (selectedMethod === 'direct') {
                // Quay lại menu chọn loại xuất hàng, xóa dữ liệu đã nhập
                resetFormData();
                $('#delivery-type-selection').show();
            }
        });
        
        // Hàm lọc danh sách phiếu cân theo loại hàng
        function filterScalesByProductType(productName) {
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
        
        // Event handler cho nút làm mới danh sách phiếu cân
        $('#btn-scale-refresh').on('click', function() {
            // Lưu trạng thái hiện tại
            localStorage.setItem('yardDeliveryState', 'scale-list');
            localStorage.setItem('yardDeliveryProductName', selectedProductType);
            localStorage.setItem('yardDeliveryProductId', selectedProductId);
            
            // Lưu trạng thái lựa chọn phần chọn loại hàng và xuất hàng
            if (selectedDeliveryType) {
                localStorage.setItem('yardDeliveryType', selectedDeliveryType);
            }
            
            // Lưu danh sách các hàng đã chọn
            if (selectedRows.length > 0) {
                localStorage.setItem('yardDeliverySelectedRows', JSON.stringify(selectedRows));
            }
            
            // Reload trang
            window.location.reload();
        });
        
        // Event handler cho việc chọn phiếu cân
        $(document).on('click', '.selectable-row', function() {
            const $this = $(this);
            const id = $this.data('id');
            
            // Chọn/bỏ chọn dòng mà không kiểm tra trùng biển số xe
            toggleScaleRowSelection($this, id);
        });
        
        // Event handler cho nút tiếp theo từ danh sách phiếu cân
        $('#btn-scale-next').on('click', function() {
            // note
            if (selectedRows.length === 0) {
                alert('Vui lòng chọn ít nhất một phiếu cân!');
                return;
            }
            
            // Kiểm tra biển số xe trùng khớp
            checkVehicleNumberMatches();
        });
        
        // Hàm kiểm tra biển số xe trùng khớp với xe đang di chuyển
        function checkVehicleNumberMatches() {
            // note
            if (selectedRows.length > 0) {
                const firstRow = selectedRows[0];
                const vehicleNumber = firstRow.vehicleNumber;
                
                // note
                if (!vehicleNumber) {
                    // Không có biển số xe, tiếp tục bình thường
                    proceedToDeliveryForm();
                    return;
                }
                
                // Kiểm tra biển số xe có trùng với xe đang vận chuyển không
                let matchingTrucks = [];
                $('.btn-truck').each(function() {
                    const truckVehicleNumber = $(this).data('vehicle');
                    // note
                    if (vehicleNumber === truckVehicleNumber) {
                        matchingTrucks.push({
                            id: $(this).data('id'),
                            vehicleNumber: truckVehicleNumber,
                            weight: $(this).data('weight'),
                            weight_formatted: $(this).text().match(/\((.*?)\)/)[1]
                        });
                    }
                });
                
                // note
                if (matchingTrucks.length > 0) {
                    // Hiển thị modal xác nhận với các tùy chọn
                    showScaleMatchTruckOptions(matchingTrucks, firstRow.id, vehicleNumber, firstRow.weight, firstRow.unitPrice, firstRow.yardId);
                } else {
                    // Không có xe trùng khớp, tiếp tục bình thường
                    proceedToDeliveryForm();
                }
            }
        }
        
        // Hiển thị modal với các tùy chọn xe tải phù hợp khi phiếu cân trùng biển số
        function showScaleMatchTruckOptions(trucks, scaleId, vehicleNumber, weight, unitPrice, yardId) {
            // Xóa tất cả các tùy chọn cũ
            $('#scaleMatchTruckOptions').empty();
            
            // Lưu thông tin phiếu cân vào localStorage để sử dụng sau
            localStorage.setItem('selectedScaleInfo', JSON.stringify({
                id: scaleId,
                vehicleNumber: vehicleNumber,
                weight: weight,
                unitPrice: unitPrice,
                yardId: yardId
            }));
            
            // Thêm tùy chọn mới cho mỗi xe tải phù hợp
            trucks.forEach(function(truck) {
                const $option = $(`
                    <button class="btn btn-primary w-100 py-3 mb-2 select-existing-truck-from-scale" 
                            data-id="${truck.id}" 
                            data-vehicle="${truck.vehicleNumber}">
                        ${truck.vehicleNumber} (${truck.weight_formatted}): Bấm để thêm vào xe này
                    </button>
                `);
                $('#scaleMatchTruckOptions').append($option);
            });
            
            // Hiển thị modal
            $('#scaleMatchTruckModal').modal('show');
        }
        
        // Xử lý khi người dùng chọn thêm vào xe tải hiện có từ phiếu cân
        $(document).on('click', '.select-existing-truck-from-scale', function() {
            const tripId = $(this).data('id');
            const selectedScaleInfo = JSON.parse(localStorage.getItem('selectedScaleInfo'));
            
            // Đóng modal
            $('#scaleMatchTruckModal').modal('hide');
            
            if (selectedScaleInfo) {
                // Thêm hidden input cho existing_trip_id
                if ($('#existing_trip_id').length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'existing_trip_id',
                        name: 'existing_trip_id',
                        value: tripId
                    }).appendTo('#deliveryForm');
                } else {
                    $('#existing_trip_id').val(tripId);
                }
                
                // Tiếp tục quy trình lập phiếu
                proceedToDeliveryForm();
            }
        });
        
        // Xử lý khi người dùng chọn tạo xe tải mới từ phiếu cân
        $('#btnCreateNewTruckFromScale').on('click', function() {
            // Đóng modal
            $('#scaleMatchTruckModal').modal('hide');
            
            // Xóa existing_trip_id nếu có
            $('#existing_trip_id').remove();
            
            // Tiếp tục quy trình lập phiếu
            proceedToDeliveryForm();
        });
        
        // Xử lý chọn/bỏ chọn dòng phiếu cân
        function toggleScaleRowSelection($row, id) {
            const singleScaleSelection = '{single_scale_selection}' === 'true';
            
            // Chọn/bỏ chọn dòng
            if ($row.hasClass('selected')) {
                $row.removeClass('selected');
                // Loại bỏ khỏi danh sách đã chọn
                selectedRows = selectedRows.filter(item => item.id !== id);
            } else {
                // Nếu SINGLE_SCALE_SELECTION = true, xóa tất cả các lựa chọn trước đó
                if (singleScaleSelection) {
                    $('.selectable-row').removeClass('selected');
                    selectedRows = [];
                }
                
                // Thêm dòng hiện tại vào lựa chọn
                $row.addClass('selected');
                
                // Lấy dữ liệu từ dòng đã chọn
                const data = {
                    id: id,
                    vehicleNumber: $row.data('soxe'),
                    weight: $row.data('klhang'),
                    unitPrice: $row.data('dongia'),
                    yardId: $row.data('yard-id')
                };
                
                // Thêm vào danh sách đã chọn
                selectedRows.push(data);
            }
            
            // Cập nhật các trường form nếu có dòng được chọn
            updateFormWithSelectedRows();
        }
        
        // Cập nhật form với dữ liệu từ dòng đã chọn
        function updateFormWithSelectedRows() {
            // note
            if (selectedRows.length > 0) {
                const first = selectedRows[0];
                $('#vehicle_number').val(first.vehicleNumber);
                $('#quantity').val(first.weight);
                $('#unit_price').val(first.unitPrice);
                $('#yard_id').val(first.yardId);
                
                // Cập nhật danh sách id đã chọn để gửi lên server
                $('#selected_items').val(selectedRows.map(item => item.id).join(','));
                
                // Cập nhật tổng tiền
                updateTotalAmount();
            } else {
                $('#vehicle_number').val('');
                $('#quantity').val('');
                $('#unit_price').val('');
                $('#selected_items').val('');
                
                // Cập nhật tổng tiền
                updateTotalAmount();
            }
        }
        
        // Tiếp tục quy trình lập phiếu sau khi chọn phiếu cân
        function proceedToDeliveryForm() {
            // note
            if (selectedRows.length > 0) {
                // Ẩn danh sách phiếu cân
                $('#scale-list-section').hide();
                
                // Hiển thị form lập phiếu xuất hàng
                $('#delivery-form-section').show();
                
                // Lấy dữ liệu từ purchase_yard_product_info
                updateProductStockInfo();
                
                // Khóa trường loại mặt hàng nếu không phải "Bán trực tiếp"
                lockCategoryField();
                
                // Khóa trường biển số xe nếu đã có dữ liệu
                lockVehicleNumberField();
                
                // Focus vào input đầu tiên
                focusFirstInput();
            } else {
                // Hiển thị thông báo nếu không có dòng nào được chọn
                alert('Vui lòng chọn ít nhất một phiếu cân.');
            }
        }
        
        // Hàm khóa trường loại mặt hàng nếu không phải "Bán trực tiếp"
        function lockCategoryField() {
            // note
            if (selectedDeliveryType !== 'sale') {
                // Khóa trường loại mặt hàng
                $('#category_id').prop('disabled', true);
                $('#category_id').parent('.form-group').addClass('opacity-75');
                
                // Thêm thông báo nếu chưa có
                // note
                if ($('#category_id').next('small.form-text').length === 0) {
                    $('#category_id').after('<small class="form-text text-muted">Loại mặt hàng không thể thay đổi khi xuất nội bộ</small>');
                }
            } else {
                // Mở khóa trường loại mặt hàng cho "Bán trực tiếp"
                $('#category_id').prop('disabled', false);
                $('#category_id').parent('.form-group').removeClass('opacity-75');
                $('#category_id').next('small.form-text').remove();
            }
        }
        
        // Hàm khóa trường biển số xe nếu đã có dữ liệu
        function lockVehicleNumberField() {
            const vehicleNumber = $('#vehicle_number').val().trim();
            // note
            if (vehicleNumber !== '') {
                // Khóa trường biển số xe
                $('#vehicle_number').prop('readonly', true);
                $('#vehicle_number').addClass('bg-light');
                
                // Thêm thông báo nếu chưa có
                // note
                if ($('#vehicle_number').next('small.form-text').length === 0) {
                    $('#vehicle_number').after('<small class="form-text text-muted">Biển số xe không thể thay đổi</small>');
                }
            } else {
                // Mở khóa trường biển số xe nếu rỗng
                $('#vehicle_number').prop('readonly', false);
                $('#vehicle_number').removeClass('bg-light');
                $('#vehicle_number').next('small.form-text').remove();
            }
        }
        
        // Hàm cập nhật thông tin từ kho
        function updateProductStockInfo() {
            const yardId = $('#yard_id').val();
            const categoryId = $('#category_id').val();
            const currencyId = $('#currency_id').val();
            
            // Kiểm tra dữ liệu đầu vào
            if (!yardId || !categoryId) {
                $('#product-info-container').hide();
                return;
            }
            
            // Chuẩn bị dữ liệu gửi đi
            const postData = {
                yard_id: yardId,
                category_id: categoryId
            };
            
            // Chỉ thêm currency_id nếu đã được chọn cụ thể
            if (currencyId) {
                postData.currency_id = currencyId;
            }
            
            $.ajax({
                url: '{site_url}yard-goods-delivery/product-info',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(response) {
                    // Process response
                    if (response.success) {
                        // Hiển thị thông tin sản phẩm
                        $('#product-stock-weight').text(response.product_info.stock_weight_formatted);
                        $('#product-average-price').text(response.product_info.average_price_formatted);
                        $('#product-info-container').show();
                        
                        // Cập nhật giá mặc định nếu đang tạo mới
                        if (selectedMethod === 'new' || selectedMethod === 'direct') {
                            $('#unit_price').val(Math.round(response.product_info.average_price));
                            // Cập nhật khối lượng mặc định bằng tồn kho hiện có
                            $('#quantity').val(Math.round(response.product_info.stock_weight));
                        }
                    } else {
                        // Ẩn thông tin sản phẩm nếu không tìm thấy
                        $('#product-info-container').hide();
                    }
                },
                error: function(xhr, status, error) {
                    // Log error
                    console.error('Error fetching product info:', error);
                    $('#product-info-container').hide();
                }
            });
            
            // Hiển thị trường tiền tệ nếu cần
            showHideCurrencySelect(yardId);
        }
        
        // Hàm hiển thị/ẩn dropdown chọn loại tiền tệ
        function showHideCurrencySelect(yardId) {
            // Kiểm tra bãi có nhiều loại tiền tệ không
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
        
        // Xử lý khi chọn bãi hoặc loại hàng
        $('#yard_id, #category_id, #currency_id').on('change', function() {
            // Kiểm tra nếu đang tạo mới
            if (selectedMethod === 'new' || selectedMethod === 'direct') {
                updateProductStockInfo();
            }
        });
        
        // Thêm CSS cho dòng có thể chọn và dòng được chọn
        $('<style>')
            .text('.selectable-row { cursor: pointer; } .selectable-row.selected { background-color: #b8e0ff !important; }')
            .appendTo('head');
        
        // Hàm reset dòng đã chọn
        function resetSelectedRows() {
            selectedRows = [];
            $('.selectable-row').removeClass('selected');
        }
        
        // Xử lý form submit
        $('#deliveryForm').on('submit', function(e) {
            e.preventDefault();
            
            // Tạm thời bật lại disabled elements để đảm bảo tất cả các trường được gửi lên server
            $('#category_id').prop('disabled', false);
            
            // Kiểm tra form trước khi submit
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                $('#category_id').prop('disabled', true); // Vô hiệu hóa lại category dropdown
                return;
            }
            
            // Đảm bảo delivery_type luôn được gửi lên server
            if (!$('#delivery_type').val()) {
                $('#delivery_type').val(selectedDeliveryType);
            }
            
            // Submit form bằng AJAX
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(data) {
                    // Xử lý kết quả
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                },
                error: function(xhr, status, error) {
                    // Xử lý lỗi
                    console.error('Error:', error);
                    alert('Đã xảy ra lỗi khi xử lý yêu cầu.');
                }
            });
            
            // Vô hiệu hóa lại category dropdown sau khi submit
            $('#category_id').prop('disabled', true);
        });
        
        // Hàm xóa dữ liệu form và đặt lại về trạng thái mặc định
        function resetFormData() {
            // Xóa dữ liệu nhập
            $('#vehicle_number').val('');
            $('#quantity').val('');
            $('#unit_price').val('');
            
            // Xóa dữ liệu phiếu cân đã chọn
            selectedRows = [];
            $('#selected_items').val('');
            
            // Xóa dữ liệu xe tải đã chọn
            selectedTruck = null;
            $('#selected_truck').val('');
            
            // Áp dụng khóa/mở khóa các trường
            lockCategoryField();
            lockVehicleNumberField();
            
            // Cập nhật thông tin kho
            updateProductStockInfo();
        }
        
        // Thêm theo dõi sự kiện thay đổi cho trường biển số xe
        $('#vehicle_number').on('blur', function() {
            lockVehicleNumberField();
        });
        
        // Thêm theo dõi sự kiện thay đổi trạng thái giao hàng
        $('#delivery_type').on('change', function() {
            selectedDeliveryType = $(this).val();
            lockCategoryField();
        });
        
        // Lọc danh sách xe tải theo loại mặt hàng
        function filterTrucksByCategory(categoryId) {
            // Ẩn tất cả các xe tải
            $('.btn-truck').hide();
            
            // Lấy danh sách xe tải theo category từ dữ liệu JSON được gán từ server
            const trucks = trucksByCategory[categoryId] || [];
            
            if (trucks.length > 0) {
                // Hiển thị xe tải thuộc category được chọn
                $('.btn-truck').each(function() {
                    const truckCategoryId = $(this).data('category');
                    if (truckCategoryId == categoryId) {
                        $(this).show();
                    }
                });
                
                // Ẩn thông báo không có xe
                $('.truck-list-section .alert-info').hide();
            } else {
                // Hiển thị thông báo không có xe thuộc loại mặt hàng đã chọn
                if ($('#no-matching-trucks-message').length === 0) {
                    const noTrucksMsg = $(`
                        <div id="no-matching-trucks-message" class="alert alert-info w-100">
                            <p class="text-center">Không có xe tải nào đang vận chuyển loại mặt hàng này.</p>
                        </div>
                    `);
                    $('#truck-list-section .card-body').append(noTrucksMsg);
                } else {
                    $('#no-matching-trucks-message').show();
                }
            }
        }
        
        // Hàm tính và cập nhật tổng tiền
        function updateTotalAmount() {
            // note
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
        
        // Event handler cho nút làm mới danh sách xe tải
        $('#btn-truck-refresh').on('click', function() {
            // Lưu trạng thái hiện tại
            localStorage.setItem('yardDeliveryState', 'truck-list');
            localStorage.setItem('yardDeliveryProductName', selectedProductType);
            localStorage.setItem('yardDeliveryProductId', selectedProductId);
            
            // Lưu trạng thái lựa chọn loại xuất hàng
            if (selectedDeliveryType) {
                localStorage.setItem('yardDeliveryType', selectedDeliveryType);
            }
            
            // Reload trang
            window.location.reload();
        });
    });
</script> 