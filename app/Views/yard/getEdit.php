<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="mb-4">Chỉnh sửa kho bãi: {yard_name}</h2>
        <form action="{site_url}yard/edit/{yard_id}" method="post">
          <div class="form-group">
            <label for="yard_code">Mã bãi</label>
            <input type="text" class="form-control" id="yard_code" name="yard_code" value="{yard_code}" required>
          </div>
          <div class="form-group mt-3">
            <label for="yard_name">Tên bãi</label>
            <input type="text" class="form-control" id="yard_name" name="yard_name" value="{yard_name}" required>
          </div>
          <div class="form-group mt-3">
            <label for="client_api_key">Client API Key</label>
            <input type="text" class="form-control" id="client_api_key" name="client_api_key" value="{client_api_key}">
          </div>
          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {status_enabled}>
            <label class="form-check-label" for="status">Hoạt động</label>
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
