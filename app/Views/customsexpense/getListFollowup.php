<style>
  /* Ẩn cột mô tả khi màn hình nhỏ hơn 1280px */
  @media (max-width: 1279px) {
    .col-mota { display: none; }
  }
</style>

<div class="container mt-3">
  <h2 class="mb-4">Danh sách phiếu theo dõi</h2>
  <!-- Nút Lập phiếu -->
  <div class="mb-3">
    <a class="btn btn-primary" href="{site_url}customs-expense/add-followup">Lập phiếu</a>
  </div>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th class="text-end">Số phiếu</th>
        <th>Ngày lập</th>
        <th>Tên mục đích</th>
        <th class="col-mota">Mô tả</th>
        <th class="text-end">Số tiền</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody>
      {followups}
      <tr>
        <td class="text-end">{voucher_number}</td>
        <td>{voucher_date}</td>
        <td>{purpose_name}</td>
        <td class="col-mota">{description}</td>
        <td class="text-end">{amount}</td>
        <td>
          <a class="btn btn-sm btn-info" href="{site_url}customs-expense/detail-followup/{id}">Xem chi tiết</a>
          <a class="btn btn-sm btn-secondary" href="{site_url}customs-expense/edit-followup/{id}">Sửa phiếu</a>
          {can_delete}
          <a class="btn btn-sm btn-danger" href="{site_url}customs-expense/delete-followup/{can_delete_id}" onclick="return confirm('Ban co chac chan xoa khong?');">Xoá phiếu</a>
          {/can_delete}
        </td>
      </tr>
      {/followups}
    </tbody>
  </table>
</div>
