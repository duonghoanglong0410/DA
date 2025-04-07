<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="mb-4">Chỉnh sửa quỹ tiền: {fund_name}</h2>
        <form action="{site_url}cash-fund/edit/{fund_id}" method="post">
          <div class="form-group">
            <label for="fund_code">Mã quỹ</label>
            <input type="text" class="form-control" id="fund_code" name="fund_code" value="{fund_code}" required>
          </div>
          <div class="form-group mt-3">
            <label for="fund_name">Tên quỹ</label>
            <input type="text" class="form-control" id="fund_name" name="fund_name" value="{fund_name}" required>
          </div>
          <h4 class="mt-4">Chọn các loại tiền tệ sử dụng</h4>
          {currencies}
          <div class="form-group mt-2">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="currency_{id}" name="currency_{id}" {if enabled}checked{/if}>
              <label class="form-check-label" for="currency_{id}">{name} ({abbreviation})</label>
            </div>
          </div>
          {/currencies}
          <div class="mt-3 d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
