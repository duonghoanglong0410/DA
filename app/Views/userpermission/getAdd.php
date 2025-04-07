<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="mb-4">Thêm Người Dùng</h2>
        <form id="addUserForm" class="getDetail" method="post" action="{site_url}user-permission/add">
          <div class="mb-3">
            <label for="username" class="form-label">Tên đăng nhập</label>
            <input type="text" name="username" id="username" class="form-control" value="{username}" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu</label>
            <input type="password" name="password" id="password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="fullname" class="form-label">Họ và tên</label>
            <input type="text" name="fullname" id="fullname" class="form-control" value="{fullname}" required>
          </div>
          <div id="errorMessage" class="text-danger mb-3"></div>
          <div class="mt-4">
            <button type="submit" class="btn btn-primary">Thêm người dùng</button>
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Giả sử header.php và footer.php đã bao gồm các liên kết CSS, JS cần thiết -->
<script>
$(document).ready(function(){
    $('#addUserForm').on('submit', function(e){
        e.preventDefault();
        // Kiểm tra xác nhận mật khẩu trên client
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        /* chèn ghi chú */ if(password !== confirmPassword){
            $('#errorMessage').html('Mật khẩu và xác nhận mật khẩu không khớp.');
            return false;
        }
        var formData = $(this).serialize();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response){
                /* chèn ghi chú */ if(response.status == 'error'){
                    $('#errorMessage').html(response.message);
                } else {
                    window.location.href = '{site_url}user-permission/user-list';
                }
            },
            error: function(){
                /* chèn ghi chú */ if(true){
                    $('#errorMessage').html('Có lỗi xảy ra. Vui lòng thử lại.');
                }
            }
        });
    });
});
</script>
