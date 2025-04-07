<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="mb-4">Chỉnh sửa thông tin người dùng</h2>
        <form id="editForm" action="{site_url}user/edit/{id}" method="post">
          <div class="form-group">
            <label for="username">Tên đăng nhập</label>
            <input type="text" class="form-control" id="username" name="username" value="{username}" readonly>
          </div>
          <div class="form-group mt-3">
            <label for="fullname">Họ tên</label>
            <input type="text" class="form-control" id="fullname" name="fullname" value="{fullname}" required>
          </div>
          <div class="form-group mt-3">
            <label for="password">Mật khẩu mới (nếu muốn thay đổi)</label>
            <input type="password" class="form-control" id="password" name="password">
          </div>
          <div class="form-group mt-3">
            <label for="confirm_password">Xác nhận mật khẩu mới</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
          </div>
          <div class="mt-4 d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{site_url}home'">Huỷ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  $("#editForm").on("submit", function(e) {
    var password = $("#password").val(),
        confirmPassword = $("#confirm_password").val(),
        regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{8,}$/;
    if(password){
       // Kiểm tra: đảm bảo mật khẩu có ít nhất 8 ký tự, chứa chữ hoa, chữ thường và ít nhất một ký tự đặc biệt
      if(!regex.test(password)){
         e.preventDefault();
         alert("Mật khẩu phải có ít nhất 8 ký tự, chứa chữ hoa, chữ thường và ít nhất một ký tự đặc biệt.");
         return false;
      }
       // Kiểm tra: so sánh mật khẩu với xác nhận mật khẩu
      if(password !== confirmPassword){
         e.preventDefault();
         alert("Mật khẩu xác nhận không khớp.");
         return false;
      }
    }
  });
});
</script>
