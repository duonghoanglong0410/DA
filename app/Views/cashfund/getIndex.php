<div class="container mt-3">
  <h2 class="mb-4">Danh sách quỹ tiền</h2>
  <a href="{site_url}cash-fund/add" class="btn btn-success mb-3">Thêm mới quỹ tiền</a>
  <div class="table-responsive">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Mã quỹ</th>
          <th>Tên quỹ</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        {funds}
        <tr>
          <td>{fund_code}</td>
          <td>{fund_name}</td>
          <td>
            <a href="{site_url}cash-fund/edit/{id}" class="btn btn-primary btn-sm">Sửa</a>
            <a href="{site_url}cash-fund/delete/{id}" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
            <a href="{site_url}cash-fund/detail/{id}" class="btn btn-info btn-sm">Chi tiết</a>
          </td>
        </tr>
        {/funds}
      </tbody>
    </table>
  </div>  
  <a class="btn btn-secondary" href="{site_url}cash-fund">Trở lại</a>
</div>
