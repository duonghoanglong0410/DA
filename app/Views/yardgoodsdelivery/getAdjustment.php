<div class="container-fluid mt-3">
    <!-- Danh sách phiếu điều chỉnh -->
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="form-box">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Danh sách phiếu điều chỉnh chờ duyệt</h5>
                </div>
                
                <!-- Bộ lọc theo bãi -->
                <div class="p-3 border-bottom">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="yard_filter" class="form-label">Lọc theo bãi:</label>
                                <select id="yard_filter" class="form-select">
                                    <option value="">Tất cả các bãi</option>
                                    {yard_options}
                                    <option value="{yard_id}">{yard_name}</option>
                                    {/yard_options}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="adjustmentTable">
                        <thead>
                            <tr>
                                <th>Biển số xe</th>
                                <th>Loại mặt hàng</th>
                                <th class="text-end">Khối lượng cũ (kg)</th>
                                <th class="text-end">Khối lượng mới (kg)</th>
                                <th class="text-end">Chênh lệch (kg)</th>
                                <th class="text-end">Đơn giá cũ</th>
                                <th class="text-end">Đơn giá mới</th>
                                <th class="text-end">Tổng tiền cũ</th>
                                <th class="text-end">Tổng tiền mới</th>
                                <th class="text-end">Chênh lệch tiền</th>
                                <th>Người tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            {adjustments}
                            <tr data-yard-id="{yard_id}">
                                <td>{vehicle_number}</td>
                                <td>{category_name}</td>
                                <td class="text-end">{old_weight_formatted}</td>
                                <td class="text-end">{new_weight_formatted}</td>
                                <td class="text-end font-weight-bold {weight_difference_class}">{weight_difference_formatted}</td>
                                <td class="text-end">{old_unit_price_formatted}</td>
                                <td class="text-end">{new_unit_price_formatted}</td>
                                <td class="text-end">{old_total_formatted}</td>
                                <td class="text-end">{new_total_formatted}</td>
                                <td class="text-end font-weight-bold {value_difference_class}">{value_difference_formatted}</td>
                                <td>{creator_name}</td>
                                <td>{!action_buttons!}</td>
                            </tr>
                            {/adjustments}
                            
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="{site_url}yard-goods-receipt" class="btn btn-secondary btn-lg py-3">Quay lại</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận huỷ phiếu -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Xác nhận huỷ phiếu</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn huỷ phiếu điều chỉnh này không?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">Huỷ phiếu</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận phiếu -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Xác nhận phiếu điều chỉnh</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xác nhận phiếu điều chỉnh này không? Hành động này sẽ cập nhật dữ liệu phiếu nhập hàng và tồn kho tương ứng.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">Xác nhận</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Khi document đã sẵn sàng
    $(document).ready(function() 
    {
        // Biến lưu trữ adjustment ID hiện tại
        let currentAdjustmentId = 0;
        
        // Sử dụng DataTables để hiển thị và phân trang dữ liệu
        var adjustmentTable = $('#adjustmentTable').DataTable(
        {
            // Cài đặt responsive
            "responsive": true,
            // Tự động điều chỉnh chiều rộng
            "autoWidth": false,
            // Ngôn ngữ tiếng Việt
            "language": 
            {
                // Đường dẫn đến file ngôn ngữ
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
            },
            // Sắp xếp mặc định theo cột đầu tiên, giảm dần
            "order": [[0, "desc"]]
        });
        
        // Xử lý khi thay đổi bộ lọc bãi
        $('#yard_filter').on('change', function() 
        {
            // Giá trị bãi đã chọn
            var selectedYardId = $(this).val();
            
            // Lọc DataTable
            if (selectedYardId) 
            {
                // Nếu đã chọn bãi cụ thể, lọc các hàng có data-yard-id khớp
                adjustmentTable.columns(0).search('').draw();
                
                // Ẩn tất cả các hàng
                $('tr[data-yard-id]').hide();
                
                // Hiển thị các hàng thuộc bãi đã chọn
                $('tr[data-yard-id="' + selectedYardId + '"]').show();
            } 
            else 
            {
                // Nếu chọn "Tất cả các bãi", hiển thị tất cả
                adjustmentTable.columns(0).search('').draw();
                $('tr[data-yard-id]').show();
            }
        });
        
        // Xử lý khi click vào nút Hủy
        $(document).on('click', '.btn-cancel-adjustment', function() 
        {
            // Lưu ID phiếu điều chỉnh
            currentAdjustmentId = $(this).data('id');
            // Hiển thị modal xác nhận
            $('#cancelModal').modal('show');
        });
        
        // Xử lý khi click vào nút Xác nhận
        $(document).on('click', '.btn-confirm-adjustment', function() 
        {
            // Lưu ID phiếu điều chỉnh
            currentAdjustmentId = $(this).data('id');
            // Hiển thị modal xác nhận
            $('#confirmModal').modal('show');
        });
        
        // Xử lý khi xác nhận huỷ phiếu
        $('#confirmCancelBtn').on('click', function() 
        {
            // Kiểm tra có ID hợp lệ không
            let isValidId = currentAdjustmentId > 0;
            
            // Chỉ thực hiện khi có ID hợp lệ
            if (isValidId) 
            {
                // Gửi request huỷ phiếu
                $.ajax(
                {
                    // URL endpoint xử lý huỷ phiếu
                    url: '{site_url}yard-goods-delivery/cancel-adjustment',
                    // Phương thức POST
                    type: 'POST',
                    // Dữ liệu gửi đi
                    data: 
                    {
                        // ID phiếu điều chỉnh cần huỷ
                        adjustment_id: currentAdjustmentId
                    },
                    // Kiểu dữ liệu trả về
                    dataType: 'json',
                    // Xử lý khi request thành công
                    success: function(data) 
                    {
                        // Ẩn modal xác nhận
                        $('#cancelModal').modal('hide');
                        
                        // Kiểm tra kết quả trả về
                        let isSuccess = data.success;
                        
                        // Nếu thành công
                        if (isSuccess) 
                        {
                            // Hiển thị thông báo
                            alert(data.message);
                            // Reload trang sau khi huỷ thành công
                            window.location.reload();
                        } 
                        else 
                        {
                            // Hiển thị thông báo lỗi
                            alert('Lỗi: ' + data.message);
                        }
                    },
                    // Xử lý khi request thất bại
                    error: function(xhr, status, error) 
                    {
                        // Ẩn modal xác nhận
                        $('#cancelModal').modal('hide');
                        // Ghi log lỗi
                        console.error('Error:', error);
                        // Hiển thị thông báo lỗi
                        alert('Đã xảy ra lỗi khi xử lý yêu cầu.');
                    }
                });
            }
        });
        
        // Xử lý khi xác nhận duyệt phiếu
        $('#confirmApproveBtn').on('click', function() 
        {
            // Kiểm tra có ID hợp lệ không
            let isValidId = currentAdjustmentId > 0;
            
            // Chỉ thực hiện khi có ID hợp lệ
            if (isValidId) 
            {
                // Gửi request xác nhận phiếu
                $.ajax(
                {
                    // URL endpoint xử lý xác nhận phiếu
                    url: '{site_url}yard-goods-delivery/confirm-adjustment',
                    // Phương thức POST
                    type: 'POST',
                    // Dữ liệu gửi đi
                    data: 
                    {
                        // ID phiếu điều chỉnh cần xác nhận
                        adjustment_id: currentAdjustmentId
                    },
                    // Kiểu dữ liệu trả về
                    dataType: 'json',
                    // Xử lý khi request thành công
                    success: function(data) 
                    {
                        // Ẩn modal xác nhận
                        $('#confirmModal').modal('hide');
                        
                        // Kiểm tra kết quả trả về
                        let isSuccess = data.success;
                        
                        // Nếu thành công
                        if (isSuccess) 
                        {
                            // Hiển thị thông báo
                            alert(data.message);
                            // Reload trang sau khi xác nhận thành công
                            window.location.reload();
                        } 
                        else 
                        {
                            // Hiển thị thông báo lỗi
                            alert('Lỗi: ' + data.message);
                        }
                    },
                    // Xử lý khi request thất bại
                    error: function(xhr, status, error) 
                    {
                        // Ẩn modal xác nhận
                        $('#confirmModal').modal('hide');
                        // Ghi log lỗi
                        console.error('Error:', error);
                        // Hiển thị thông báo lỗi
                        alert('Đã xảy ra lỗi khi xử lý yêu cầu.');
                    }
                });
            }
        });
        
    });
</script> 