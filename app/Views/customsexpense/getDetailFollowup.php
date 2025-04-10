<div class="container mt-3">
  <h2 class="mb-4">Chi tiết phiếu theo dõi</h2>
  <div class="card mb-4">
    <div class="card-header">Thông tin phiếu</div>
    <div class="card-body">
      <p><strong>Số phiếu:</strong> {voucher_number}</p>
      <p><strong>Ngày lập:</strong> {voucher_date}</p>
      <p><strong>Tên mục đích:</strong> {purpose_name}</p>
      <p><strong>Số tiền:</strong> {amount}</p>
      <p><strong>Mô tả:</strong> {description}</p>
      <p><strong>Người lập phiếu:</strong> {creator_fullname} ({creator_username})</p>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Danh sách công nợ đã trả</div>
    <div class="card-body">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Ngày lập</th>
            <th class="text-end">Số tiền</th>
            <th>Mô tả</th>
            <th>Người lập</th>
          </tr>
        </thead>
        <tbody>
          {settlements}
          <tr>
            <td>{created_date}</td>
            <td class="text-end">{amount}</td>
            <td>{description}</td>
            <td>{creator_fullname} ({creator_username})</td>
          </tr>
          {/settlements}
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    <button type="button" class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
  </div>
</div>
