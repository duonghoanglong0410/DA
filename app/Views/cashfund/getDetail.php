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
              <th class="text-center">Tên tiền tệ</th>
              <th class="text-center">Viết tắt</th>
              <th class="text-center">Số dư</th>
            </tr>
          </thead>
          <tbody>
            {cfCurrencies}
            <tr>
              <td>{currency_name}</td>
              <td class="text-center">{abbreviation}</td>
              <td class="text-end">{balance}</td>
            </tr>
            {/cfCurrencies}
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <a class="btn btn-secondary" href="{site_url}cash-fund">Trở lại</a>
</div>
