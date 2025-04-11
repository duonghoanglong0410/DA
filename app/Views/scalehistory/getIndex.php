<div class="container-fluid mt-3"> <!-- Sử dụng container-fluid cho bảng rộng -->
    <h2 class="mb-4">Lịch sử dữ liệu cân của bãi</h2>

    <!-- Form Lọc và Tìm kiếm -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" action="{site_url}scale-history">
                <div class="row g-3">
                    <!-- Lọc theo Bãi -->
                    <div class="col-md-3">
                        <label for="yard_id" class="form-label">Bãi cân</label>
                        <select id="yard_id" name="yard_id" class="form-select">
                            <option value="">-- Tất cả bãi --</option>
                            {yards}
                            <option value="{id}" {selected}>{yard_name} ({yard_code})</option>
                            {/yards}
                        </select>
                    </div>

                     <!-- Lọc theo Chế độ -->
                     <div class="col-md-3">
                         <label for="chedo" class="form-label">Chế độ cân</label>
                         <select id="chedo" name="chedo" class="form-select">
                            {cheDoOptions}
                            <option value="{value}" {selected}>{name}</option>
                            {/cheDoOptions}
                         </select>
                     </div>

                    <!-- Lọc theo Loại hàng -->
                     <div class="col-md-3">
                         <label for="loaihang" class="form-label">Loại hàng</label>
                         <select id="loaihang" name="loaihang" class="form-select">
                            {loaiHangOptions}
                            <option value="{value}" {selected}>{name}</option>
                            {/loaiHangOptions}
                         </select>
                     </div>
                     
                     <!-- Lọc theo Loại phiếu -->
                     <div class="col-md-3">
                         <label for="phieu_type" class="form-label">Loại phiếu</label>
                         <select id="phieu_type" name="phieu_type" class="form-select">
                            {phieuTypeOptions}
                            <option value="{value}" {selected}>{name}</option>
                            {/phieuTypeOptions}
                         </select>
                     </div>

                    <!-- Lọc theo Ngày Cân -->
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Từ ngày</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{filter_start_date}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Đến ngày</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{filter_end_date}">
                    </div>

                    <!-- Tìm kiếm -->
                    <div class="col-md-6">
                        <label for="search_term" class="form-label">Tìm kiếm (Số xe, Ghi chú, Tên lái xe, Tên khách hàng)</label>
                        <input type="text" id="search_term" name="search_term" class="form-control" placeholder="Nhập từ khóa..." value="{searchTerm}">
                    </div>

                    <!-- Nút bấm -->
                    <div class="col-md-12 d-flex justify-content-between align-items-end">
                        <div>
                            <a href="{site_url}scale-history/stats" class="btn btn-info">Xem thống kê</a>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary me-2">Lọc / Tìm kiếm</button>
                            <a href="{site_url}scale-history" class="btn btn-secondary">Xóa bộ lọc</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng Dữ liệu -->
    <div class="table-responsive"> <!-- Cho phép cuộn ngang trên màn hình nhỏ -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr class="table-primary">
                    <th>Mã Bãi</th>
                    <th>Tên Bãi</th>
                    <th>Loại phiếu</th>
                    <th>Tên Khách Hàng</th>
                    <th>Số Xe</th>
                    <th>Loại Hàng</th>
                    <th class="text-end">KL Có Tải</th>
                    <th class="text-end">KL Không Tải</th>
                    <th class="text-end">KL Hàng</th>
                    <th class="text-end">Đơn Giá</th>
                    <th class="text-end">Thành Tiền</th>
                    <th>Ngày Cân</th>
                    <th>Chế Độ</th>
                    <th>Ghi Chú</th>
                    <th>Tên Lái Xe</th>
                    <th>Người Cân</th>
                    <th>Số Phiếu In</th>
                    <!-- Thêm các cột khác nếu cần -->
                </tr>
            </thead>
            <tbody>
                {scaleHistory}
                <tr>
                    <td>{yard_code}</td>
                    <td>{yard_name}</td>
                    <td>{Msp}</td>
                    <td>{Tenkhachhang}</td>
                    <td>{Soxe}</td>
                    <td>{Loaihang}</td>
                    <td class="text-end">{KLcotai_formatted}</td>
                    <td class="text-end">{KLkhongtai_formatted}</td>
                    <td class="text-end">{KLhang_formatted}</td>
                    <td class="text-end">{Dongia_formatted}</td>
                    <td class="text-end">{Thanhtien_formatted}</td>
                    <td>{Ngaycan_formatted}</td>
                    <td>{chedo}</td>
                    <td>{Ghichu}</td>
                    <td>{Tenlaixe}</td>
                    <td>{Nguoican}</td> <!-- Cần join với bảng users nếu muốn hiển thị tên -->
                    <td>{Sophieuin}</td>
                    <!-- Thêm các cột khác nếu cần -->
                </tr>
                {/scaleHistory}
                {noDataMessage}
                <tr>
                    <td colspan="17" class="text-center">{message}</td><!-- Cập nhật colspan --> 
                </tr>
                {/noDataMessage}
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    {!pagination!}

</div>
