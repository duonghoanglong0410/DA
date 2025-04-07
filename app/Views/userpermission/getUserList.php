<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="form-box">
        <h2 class="mb-4">Danh sách người dùng</h2>
        <div class="mb-3 text-end">
          <a href="{site_url}user-permission/add" class="btn btn-success">Thêm người dùng</a>
        </div>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Tên đăng nhập</th>
              <th>Họ và tên</th>
              <th>Quyền hạn</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {users}
            <tr>
              <td>{username}</td>
              <td>{fullname}</td>
              <td>{role_names}</td>
              <td>
                <a href="{site_url}user/edit/{id}" class="btn btn-sm btn-primary">Sửa thông tin</a>
                <a href="{site_url}user-permission/edit/{id}" class="btn btn-sm btn-warning">Cập nhật quyền</a>
                <a href="{site_url}user-permission/lock/{id}" class="btn btn-sm btn-danger" onclick="return confirm('Ban co chac chan muon khoa tai khoan nay khong?');">Khoá tài khoản</a>
              </td>
            </tr>
            {/users}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
