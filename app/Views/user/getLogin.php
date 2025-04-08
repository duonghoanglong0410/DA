<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="text-center"><i class="fas fa-user-circle"></i> Đăng nhập</h2>
        <form action="{site_url}user/authencation" method="post" class="getDetail">
          {error}
          <div class="alert alert-danger">
            {mess}
          </div>
          {/error}
          <div class="mb-3">
            <label for="username"><i class="fas fa-user"></i> Tên đăng nhập</label>
            <input type="text" name="username" id="username" class="form-control" value="" required>
          </div>
          <div class="mb-3">
            <label for="password"><i class="fas fa-lock"></i> Mật khẩu</label>
            <input type="password" name="password" id="password" class="form-control" required>
          </div>
          <div class="form-check">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label for="remember" class="form-check-label">Ghi nhớ đăng nhập tự động</label>
          </div>
          <button type="submit" class="btn btn-primary btn-block mt-3">Đăng nhập</button>
        </form>
      </div>
    </div>
  </div>
</div>
