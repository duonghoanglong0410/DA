<div class="container mt-3">
  <div class="row">
    <div class="col-12">
      <div class="form-box getDetail">
        <h2 class="mb-4">Tổng quan kho bãi</h2>

        <!-- Thông tin tồn kho sản phẩm -->
        <div class="card mb-4">
          <div class="card-header bg-success text-white">
            <h5 class="mb-0">Thông tin tồn kho</h5>
          </div>
            <div class="table-responsive">
              <table class="table table-striped table-bordered" id="product-table">
                <thead>
                  <tr>
                    <th>Kho bãi</th>
                    <th>Loại</th>
                    <th class="text-end">Tồn (kg)</th>
                    <th class="text-end desktop-cell">Giá BQ</th>
                    <th class="text-end desktop-cell">Giá trị</th>
                  </tr>
                </thead>
                <tbody>
                  {yardProducts}
                  <tr class="product-row">
                    <td>{yard_name}</td>
                    <td>{category_name}</td>
                    <td class="text-end">
                      {stock_weight_formatted}
                      <a href="{site_url}yard/stock/{id}" class="btn btn-primary">Điều chỉnh</a>
                    </td>
                    <td class="text-end desktop-cell">{average_price_formatted}{symbol}</td>
                    <td class="text-end desktop-cell">{stock_value_formatted}{symbol}</td>
                  </tr>
                  <tr class="mobile-row">
                    <td>Giá BQ</td>
                    <td class="text-end">{average_price_formatted}{symbol}</td>
                    <td class="text-end">{stock_value_formatted}{symbol}</td>
                  </tr>
                  {/yardProducts}
                </tbody>
              </table>
            </div>
        </div>

        <!-- Thông tin quỹ tiền -->
        <div class="card mb-4">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0">Thông tin quỹ tiền</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-bordered" id="currency-table">
                <thead>
                  <tr>
                    <th>Kho bãi</th>
                    <th class="text-end">Số dư</th>
                  </tr>
                </thead>
                <tbody>
                  {yardCurrencies}
                  <tr class="currency-row" data-yard-id="{yard_id}">
                    <td>{yard_name}</td>
                    <td class="text-end">{balance_formatted} {abbreviation}</td>
                  </tr>
                  {/yardCurrencies}
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Nút điều hướng -->
        <div class="mt-3 d-flex justify-content-between">
          <button type="button" class="btn btn-secondary" onclick="window.history.back();">Quay lại</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    // Xử lý lọc theo kho bãi
    $('#yard_filter').on('change', function() {
      var selectedYardId = $(this).val();
      
      if (selectedYardId === 'all') {
        // Hiển thị tất cả các kho bãi
        $('.yard-card').show();
        $('.product-row').show();
        $('.currency-row').show();
      } else {
        // Hiển thị chỉ kho bãi được chọn
        $('.yard-card').hide();
        $('.yard-card[data-yard-id="' + selectedYardId + '"]').show();
        
        $('.product-row').hide();
        $('.product-row[data-yard-id="' + selectedYardId + '"]').show();
        
        $('.currency-row').hide();
        $('.currency-row[data-yard-id="' + selectedYardId + '"]').show();
      }
    });
  });
</script>
