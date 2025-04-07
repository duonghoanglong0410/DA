<div class="container mt-3">
  <h2 class="mb-4">Chi tiết quỹ tiền</h2>
  <div class="card">
    <div class="card-body">
      <p><strong>Mã quỹ:</strong> {fund_code}</p>
      <p><strong>Tên quỹ:</strong> {fund_name}</p>
      <h4 class="mt-4">Thông tin tiền tệ</h4>
      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Tên tiền tệ</th>
              <th>Viết tắt</th>
              <th>Số dư</th>
            </tr>
          </thead>
          <tbody>
            {cfCurrencies}
            <tr>
              <td>{currency_name}</td>
              <td>{abbreviation}</td>
              <td>{balance}</td>
            </tr>
            {/cfCurrencies}
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <button type="button" class="btn btn-secondary mt-3" onclick="window.history.back();">Trở lại</button>
</div>
