<div class="container mt-3">
  <h2 class="mb-4">Danh sách kho bãi</h2>
  <a href="{site_url}yard/add" class="btn btn-success mb-3">Thêm mới kho bãi</a>
  <div class="table-responsive">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Mã bãi</th>
          <th>Tên bãi</th>
          <th class="d-none d-md-table-cell">Client API Key</th>
          <th>Trạng thái</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        {yards}
        <tr>
          <td>{yard_code}</td>
          <td>{yard_name}</td>
          <td class="d-none d-md-table-cell">{client_api_key}</td>
          <td>{status_text}</td>
          <td>
            <a href="{site_url}yard/edit/{id}" class="btn btn-primary btn-sm">Sửa</a>
            <a href="{site_url}yard/delete/{id}" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
          </td>
        </tr>
        {/yards}
      </tbody>
    </table>
  </div>
  <button type="button" class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
</div>
