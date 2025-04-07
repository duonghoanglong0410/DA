<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="mb-4">Sửa nhà máy</h2>
        <form id="buyerEditForm" action="{site_url}buyer/edit/{buyer_id}" method="post" class="getDetail">
          <div class="mb-3">
            <label for="name" class="form-label">Tên nhà máy</label>
            <input type="text" name="name" id="name" class="form-control" value="{name}" required>
          </div>
          <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ</label>
            <input type="text" name="address" id="address" class="form-control" value="{address}" required>
          </div>
          <hr class="my-3">
          <h5 class="mb-3">Chọn các loại tiền tệ sử dụng</h5>
          <div id="currencyList">
            {currencies}
            <div class="form-check mb-2">
              <input type="checkbox" class="form-check-input" name="currency_active[{id}]" id="currency_active_{id}" value="1" {active}>
              <label class="form-check-label" for="currency_active_{id}">{name} ({abbreviation})</label>
            </div>
            {/currencies}
          </div>
          <div id="errorMessage" class="text-danger mb-3"></div>
          <div class="mt-4">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
    $("#buyerEditForm").submit(function(e){
        /* chèn ghi chú */ if ($("input[name^='currency_active']:checked").length == 0) {
            $("#errorMessage").html("Vui lòng chọn ít nhất một loại tiền tệ.");
            e.preventDefault();
        }
    });
});
</script>
