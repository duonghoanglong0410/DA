<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="form-box">
        <h2 class="mb-4">Danh sách chủng loại mặt hàng</h2>
        <div class="mb-3 d-flex justify-content-between">
          <a href="{site_url}trang-chu" class="btn btn-secondary">Trở về trang chủ</a>
          <a href="{site_url}product-category/add" class="btn btn-success">Thêm chủng loại</a>
        </div>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Tên mặt hàng</th>
              <th>Viết tắt</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {categories}
            <tr>
              <td>{name}</td>
              <td>{abbreviation}</td>
              <td>
                <a href="{site_url}product-category/edit/{id}" class="btn btn-sm btn-primary">Sửa</a>
                <a href="{site_url}product-category/delete/{id}" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xoá chủng loại này không?');">Xoá</a>
              </td>
            </tr>
            {/categories}
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
