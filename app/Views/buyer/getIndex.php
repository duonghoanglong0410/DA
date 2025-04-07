<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="form-box">
        <h2 class="mb-4">Danh sách nhà máy</h2>
        <div class="mb-3 d-flex justify-content-between">
          <a href="{site_url}trang-chu" class="btn btn-secondary">Trở về trang chủ</a>
          <a href="{site_url}buyer/add" class="btn btn-success">Thêm nhà máy</a>
        </div>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Tên nhà máy</th>
              <th>Địa chỉ</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {buyers}
            <tr>
              <td>{name}</td>
              <td>{address}</td>
              <td>
                <a href="{site_url}buyer/edit/{id}" class="btn btn-sm btn-primary">Sửa</a>
                <a href="{site_url}buyer/delete/{id}" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xoá nhà máy này không?');">Xoá</a>
              </td>
            </tr>
            {/buyers}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
