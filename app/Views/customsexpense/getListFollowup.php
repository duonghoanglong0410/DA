<div class="container mt-3">
  <h2 class="mb-4">Danh sách phiếu theo dõi</h2>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Ngày lập</th>
        <th>Tên mục đích</th>
        <th>Mô tả</th>
        <th>Số tiền</th>
      </tr>
    </thead>
    <tbody>
      {followups}
      <tr>
        <td>{voucher_date}</td>
        <td>{purpose_name}</td>
        <td>{description}</td>
        <td>{amount}</td>
      </tr>
      {/followups}
    </tbody>
  </table>
</div>
