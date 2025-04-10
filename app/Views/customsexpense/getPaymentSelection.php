<div class="container mt-3">
  <h2 class="mb-4">Lựa chọn phiếu theo dõi để thanh toán</h2>
  <form action="{site_url}customs-expense/post-payment-followup" method="post">
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Chọn</th>
          <th>Ngày lập</th>
          <th>Tên mục đích</th>
          <th>Mô tả</th>
          <th class="text-end">Số tiền</th>
          <th class="text-end">Số tiền chưa thanh toán</th>
        </tr>
      </thead>
      <tbody>
        {followups}
        <tr>
          <td>
            <!-- Thêm thuộc tính data-outstanding để lưu giá trị số chưa thanh toán (raw) -->
            <input type="checkbox" name="selected[]" value="{id}" data-outstanding="{raw_outstanding}">
          </td>
          <td>{voucher_date}</td>
          <td>{purpose_name}</td>
          <td>{description}</td>
          <td class="text-end">{amount}</td>
          <td class="text-end">{outstanding}</td>
        </tr>
        {/followups}
      </tbody>
    </table>
    <div class="mb-3">
      <label for="payment_date" class="form-label">Ngày thanh toán</label>
      <input type="date" class="form-control" id="payment_date" name="payment_date" value="{current_date}" required>
    </div>
    <div class="mb-3">
      <label for="total_payment" class="form-label">Số tiền thanh toán</label>
      <!-- Giá trị này mặc định được tính dựa trên checkbox, người dùng có thể thay đổi nhưng không vượt quá tổng số tiền tính được -->
      <input type="number" class="form-control" id="total_payment" name="total_payment" value="0" required step="1">
      <small class="text-muted">Giá trị không được vượt quá tổng số tiền chưa thanh toán của các phiếu đã chọn.</small>
    </div>
    <div class="mt-3 d-flex justify-content-between">
      <button type="submit" class="btn btn-primary">Thanh toán</button>
      <button type="button" class="btn btn-secondary" onclick="window.history.back();">Huỷ bỏ</button>
    </div>
  </form>
</div>

<script>
  // Hàm chuyển đổi chuỗi có dấu phân cách ngàn thành số thực
  function parseNumber(str) {
    // Loại bỏ dấu chấm
    return parseInt(str.replace(/\./g, ''), 10) || 0;
  }

  // Hàm tính tổng số tiền chưa thanh toán dựa vào checkbox được chọn
  function updateTotalPayment() {
    var checkboxes = document.querySelectorAll('input[name="selected[]"]:checked');
    var total = 0;
    checkboxes.forEach(function(chk) {
      total += parseInt(chk.getAttribute('data-outstanding'), 10) || 0;
    });
    // Cập nhật giá trị input "Số tiền thanh toán"
    document.getElementById('total_payment').value = total;
    // Cập nhật thuộc tính max cho input nếu muốn sử dụng thuộc tính HTML5
    document.getElementById('total_payment').setAttribute('max', total);
  }

  // Lắng nghe sự kiện thay đổi trạng thái của checkbox
  document.querySelectorAll('input[name="selected[]"]').forEach(function(chk) {
    chk.addEventListener('change', updateTotalPayment);
  });

  // Kiểm tra khi người dùng thay đổi trực tiếp giá trị "Số tiền thanh toán"
  document.getElementById('total_payment').addEventListener('change', function() {
    var maxVal = parseInt(this.getAttribute('max'), 10) || 0;
    var entered = parseInt(this.value, 10) || 0;
    if (entered > maxVal) {
      alert('Số tiền thanh toán không được vượt quá tổng số tiền chưa thanh toán: ' + maxVal);
      this.value = maxVal;
    }
  });
</script>
