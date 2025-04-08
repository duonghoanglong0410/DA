<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="form-box">
        <h2 class="mb-4">Danh sách kho</h2>
        <ul class="list-group">
          {warehouses}
            <li class="list-group-item">
              <strong>{name}</strong> - {address}
            </li>
          {/warehouses}
        </ul>
        
        <div class="mt-3">
          <a class="btn btn-primary" href="{site_url}warehouse/add">Thêm kho mới</a>
          <button class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
        </div>
      </div>
    </div>
  </div>
</div>
