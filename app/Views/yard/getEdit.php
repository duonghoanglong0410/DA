<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="mb-4">Sửa Kho Bãi</h2>
        <form action="{site_url}yard/edit/{yard_id}" method="post" class="getDetail">
          <div class="mb-3">
            <label for="yard_code" class="form-label">Mã Kho</label>
            <input type="text" name="yard_code" id="yard_code" class="form-control" value="{yard_code}" required>
          </div>
          <div class="mb-3">
            <label for="yard_name" class="form-label">Tên Kho</label>
            <input type="text" name="yard_name" id="yard_name" class="form-control" value="{yard_name}" required>
          </div>
          <div class="mb-3">
            <label for="client_api_key" class="form-label">API Key</label>
            <input type="text" name="client_api_key" id="client_api_key" class="form-control" value="{client_api_key}">
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Trạng thái</label>
            <select name="status" id="status" class="form-select">
              <option value="1" {status_active}>Hoạt động</option>
              <option value="0" {status_inactive}>Ngừng</option>
            </select>
          </div>
          <hr class="my-3">
          <h5 class="mb-3">Chọn các loại tiền tệ sử dụng</h5>
          {currencies}
          <div class="form-check mb-2">
            <input type="checkbox" class="form-check-input" name="currency_active[{id}]" id="currency_active_{id}" value="1" {active}>
            <label class="form-check-label" for="currency_active_{id}">{name} ({abbreviation})</label>
            <!-- {balance_input} chứa HTML hidden input nếu quỹ tiền đang bật -->
            {balance_input}
          </div>
          {/currencies}
          <div class="mt-4">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
