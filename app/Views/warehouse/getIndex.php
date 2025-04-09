<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="form-box">
        <h2 class="mb-4">Danh sách Kho</h2>
        <ul class="list-group">
          {warehouses}
          <li class="list-group-item">
            <div>
              <strong>{name}</strong> - {address} <br>
              <em>Người quản lý: {managers}</em>
            </div>
            <div class="mt-2 d-flex justify-content-end">
              <a href="{site_url}warehouse/edit/{id}" class="btn btn-warning btn-sm me-2 mr-2">
                Chỉnh sửa
              </a>
              <a href="{site_url}warehouse/delete/{id}" class="btn btn-danger btn-sm me-2 mr-2"
                onclick="return confirm('Bạn có chắc chắn xoá kho này?');">
                Xoá
              </a>
            </div>
          </li>
          {/warehouses}
        </ul>
        <div class="mt-4 d-flex justify-content-between">
          <a class="btn btn-primary" href="{site_url}warehouse/add">Thêm kho mới</a>
          <a class="btn btn-secondary" href="{site_url}warehouse">Trở lại</a>
        </div>
      </div>
    </div>
  </div>
</div>