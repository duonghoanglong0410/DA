<div class="container mt-3">
  <h2 class="mb-4">Danh sách phiếu thu/chi</h2>
  <!-- Nút Lập phiếu chuyển đến trang chọn loại phiếu -->
  <div class="mb-3">
    <a class="btn btn-primary" href="{site_url}customs-expense/choose-cash-voucher">Lập phiếu</a>
  </div>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Ngày</th>
        <th class="text-end">Tiền thu</th>
        <th class="text-end">Tiền chi</th>
        <th class="text-end">Mã phiếu theo dõi</th>
        <th>Mục đích</th>
        <th>Mô tả</th>
      </tr>
    </thead>
    <tbody>
      {journals}
      <tr>
        <td>{created_date}</td>
        <td class="text-end">{thu}</td>
        <td class="text-end">{chi}</td>
        <td class="text-end">{!voucher_link!}</td>
        <td>{purpose_name}</td>
        <td>{description}</td>
      </tr>
      {/journals}
    </tbody>
  </table>
  
  {!pagination!}
</div>
