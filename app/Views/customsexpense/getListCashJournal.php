<div class="container mt-3">
  <h2 class="mb-4">Danh sách phiếu thu/chi</h2>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Ngày</th>
        <th>Tiền thu</th>
        <th>Tiền chi</th>
        <th>Mục đích</th>
        <th>Mô tả</th>
      </tr>
    </thead>
    <tbody>
      {journals}
      <tr>
        <td>{created_date}</td>
        <td>{thu}</td>
        <td>{chi}</td>
        <td>{purpose_name}</td>
        <td>{description}</td>
      </tr>
      {/journals}
    </tbody>
  </table>
</div>
