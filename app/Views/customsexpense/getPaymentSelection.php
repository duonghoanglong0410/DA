<div class="container mt-3">
  <div class="row">
    <div class="col-12">
      <h2 class="mb-4">Thanh toán công nợ - Chọn phiếu theo dõi</h2>
      <form action="{site_url}customs-expense/payment-followup" method="post">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Chọn</th>
              <th>Ngày lập</th>
              <th>Số tiền</th>
              <th>Mô tả</th>
              <th>Số tiền chưa thanh toán</th>
            </tr>
          </thead>
          <tbody>
            {followups}
            <tr>
              <td>
                <input type="checkbox" name="selected[]" value="{id}">
              </td>
              <td>{created_date}</td>
              <td>{amount}</td>
              <td>{description}</td>
              <td>{unsettled_amount}</td>
            </tr>
            {/followups}
          </tbody>
        </table>
        <div class="mb-3">
          <label for="payment_date" class="form-label">Ngày thanh toán</label>
          <input type="date" class="form-control" id="payment_date" name="payment_date" required>
        </div>
        <div class="mt-3 d-flex justify-content-between">
          <button type="submit" class="btn btn-primary">Thanh toán</button>
          <button type="button" class="btn btn-secondary" onclick="window.history.back();">Huỷ bỏ</button>
        </div>
      </form>
    </div>
  </div>
</div>
