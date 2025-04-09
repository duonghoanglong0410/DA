<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box getDetail">
        <h2 class="mb-4">Thanh toán công nợ không theo phiếu theo dõi</h2>
        <form action="{site_url}customs-expense/payment-non-followup" method="post">
          <div class="mb-3">
            <label for="payment_date" class="form-label">Ngày thanh toán</label>
            <input type="date" class="form-control" id="payment_date" name="payment_date" required>
          </div>
          <div class="mb-3">
            <label for="amount" class="form-label">Số tiền thanh toán</label>
            <input type="number" class="form-control" id="amount" name="amount" required step="0.01">
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
          </div>
          <div class="mt-3 d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Thanh toán</button>
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Huỷ bỏ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
