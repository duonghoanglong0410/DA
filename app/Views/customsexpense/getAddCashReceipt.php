<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box getDetail">
        <h2 class="mb-4">Lập phiếu thu tiền</h2>
        <form action="{site_url}customs-expense/add-cash-receipt" method="post">
          <div class="mb-3">
            <label for="created_date" class="form-label">Ngày lập phiếu</label>
            <input type="date" class="form-control" id="created_date" name="created_date" value="{current_date}" required>
          </div>
          <div class="mb-3">
            <label for="amount" class="form-label">Số tiền</label>
            <input type="number" class="form-control" id="amount" name="amount" required step="0.01">
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
          </div>
          <div class="mt-3 d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Lưu phiếu</button>
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
