<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="mb-4">Chỉnh sửa quỹ tiền: {fund_name}</h2>
        <form action="cash-fund/edit/{fund_id}" method="post">
          <div class="form-group">
            <label for="fund_code">Mã quỹ</label>
            <input type="text" class="form-control" id="fund_code" name="fund_code" value="{fund_code}" required>
          </div>
          <div class="form-group mt-3">
            <label for="fund_name">Tên quỹ</label>
            <input type="text" class="form-control" id="fund_name" name="fund_name" value="{fund_name}" required>
          </div>
          <div class="mt-3 d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
