<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="form-box">
        <h2 class="mb-4">Danh sách người dùng</h2>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Tên đăng nhập</th>
              <th>Họ và tên</th>
              {roles}
              <th>{name}</th>
              {/roles}
            </tr>
          </thead>
          <tbody>
            {users}
            <tr>
              <td>{username}</td>
              <td>{fullname}</td>
              {roles}
              <td>{role_{id}}</td>
              {/roles}
            </tr>
            {/users}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
