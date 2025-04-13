<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="mb-4">Điều chỉnh tồn kho</h2>

        <div class="card mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-sm-6">
                <p><strong>Kho bãi:</strong> {yard_name}</p>
                <p><strong>Mã bãi:</strong> {yard_code}</p>
              </div>
              <div class="col-sm-6">
                <p><strong>Loại hàng:</strong> {category_name}</p>
                <p><strong>Tiền tệ:</strong> {currency_symbol}</p>
              </div>
            </div>
          </div>
        </div>

        <form id="adjustmentForm">
          <div class="mb-3">
            <label for="stock_weight" class="form-label">Tồn kho (kg)</label>
            <input type="text" class="form-control text-end" id="stock_weight" name="stock_weight" 
                   value="{stock_weight_formatted}" required>
          </div>

          <div class="mb-3">
            <label for="average_price" class="form-label">Giá bình quân</label>
            <input type="text" class="form-control text-end" id="average_price" name="average_price" 
                   value="{average_price_formatted}" required>
          </div>

          <div class="mt-3 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Quay lại</button>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Format inputs as numbers
    $('#stock_weight, #average_price').on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('vi-VN');
        }
        $(this).val(value);
    });

    $('#adjustmentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Đang xử lý...');

        // Parse formatted numbers
        let stockWeight = $('#stock_weight').val().replace(/[^0-9]/g, '');
        let averagePrice = $('#average_price').val().replace(/[^0-9]/g, '');

        // Store original values for unsaved changes check
        const originalStockWeight = '{stock_weight}';
        const originalAveragePrice = '{average_price}';

        $.ajax({
            url: '{site_url}yard/stock/{id}',
            type: 'POST',
            data: {
                stock_weight: stockWeight,
                average_price: averagePrice
            },
            dataType: 'json',
            success: function(response) {
                //
                if (response.success) {
                    alert(response.message);
                    hasChanges = false; // Clear unsaved changes flag
                    window.location.href = '{site_url}yard/dashboard';
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Đã xảy ra lỗi khi xử lý yêu cầu.');
            },
            complete: function() {
                // Restore button state
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Check for unsaved changes
    let hasChanges = false;
    $('#stock_weight, #average_price').on('change', function() {
        hasChanges = true;
    });

    window.addEventListener('beforeunload', function(e) {
        //
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});
</script>

