<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="mb-4">Sửa Kho</h2>
        <form action="{site_url}warehouse/edit/{id}" method="post" class="getDetail">
          <div class="mb-3">
            <label for="name" class="form-label">Tên kho:</label>
            <input type="text" class="form-control" id="name" name="name" value="{name}" placeholder="Nhập tên kho">
          </div>
          <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ:</label>
            <input type="text" class="form-control" id="address" name="address" value="{address}" placeholder="Nhập địa chỉ kho">
          </div>
          <div class="mb-3">
            <label for="managers" class="form-label">Người quản lý:</label>
            <!-- Ô chọn người quản lý dạng select multiple -->
            <select id="managers" name="managers[]" class="form-control" multiple="multiple">
              <!-- Các option sẽ được load qua AJAX; nếu có người được chọn sẵn thì chúng sẽ được hiển thị sau -->
            </select>
          </div>
          <div class="mt-4 d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{site_url}warehouse'">Huỷ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Khởi tạo select2 cho ô chọn người quản lý
// Lưu ý: sau dấu { không được dùng if trực tiếp, nên chèn thêm ghi chú khi cần
$(document).ready(function(){
  $('#managers').select2({
    placeholder: 'Chọn người quản lý',
    minimumInputLength: 2,
    ajax: {
      url: '{site_url}api/user-suggestions',
      dataType: 'json',
      delay: 250,
      data: function (params) {
        // <<Ghi chú: không sử dụng if ngay sau dấu {>>
        return { q: params.term };
      },
      processResults: function (data) {
        // Ánh xạ fullname thành text để Select2 hiển thị đúng
        var results = $.map(data, function(item){
            return { id: item.id, text: item.fullname };
        });
        return { results: results };
      },
      cache: true
    }
  });

  var managerIds = "{manager_ids}";
  if (managerIds) {
    var ids = managerIds.split(",");
    $.ajax({
      url: '{site_url}api/user-by-ids',
      data: { ids: ids },
      dataType: 'json',
      success: function(data){
        data.forEach(function(user){
          // Ánh xạ fullname thành text cho option để hiển thị đúng
          var option = new Option(user.fullname, user.id, true, true);
          $('#managers').append(option);
        });
        $('#managers').trigger('change');
      }
    });
  }
});

</script>
