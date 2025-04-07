<div class="container mt-3">
  <h2 class="mb-4">Danh sách quỹ tiền</h2>
  <a href="cash-fund/add" class="btn btn-success mb-3">Thêm mới quỹ tiền</a>
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
            <a href="cash-fund/edit/{id}" class="btn btn-primary btn-sm">Sửa</a>
            <a href="cash-fund/delete/{id}" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?');">Xóa</a>
          </td>
        </tr>
        {/funds}
      </tbody>
    </table>
  </div>
  <button type="button" class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
</div>
