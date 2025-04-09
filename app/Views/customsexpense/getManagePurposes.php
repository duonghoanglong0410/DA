<div class="container mt-3">
  <h2 class="mb-4">Quản lý danh mục mục đích</h2>
  <a class="btn btn-primary mb-3" href="{site_url}customs-expense/add-purpose">Thêm mới</a>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Tên mục đích</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody>
      {purposes}
      <tr>
        <td>{purpose_name}</td>
        <td>
          <a class="btn btn-sm btn-secondary" href="{site_url}customs-expense/edit-purpose/{id}">Thay đổi</a>
          <a class="btn btn-sm btn-danger" href="{site_url}customs-expense/delete-purpose/{id}" onclick="return confirm('Ban co chac chan xoa khong?');">Xoá</a>
        </td>
      </tr>
      {/purposes}
    </tbody>
  </table>
</div>
