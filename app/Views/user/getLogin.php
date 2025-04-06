<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Đăng nhập</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Font Awesome CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <h2 class="mt-5 text-center"><i class="fas fa-user-circle"></i> Đăng nhập</h2>
        {error}
        <div class="alert alert-danger">
          {mess}
        </div>
        {/error}

        <form action="{site_url}user/authencation" method="post" id="loginForm">
          <div class="form-group">
            <label for="username"><i class="fas fa-user"></i> Tên đăng nhập</label>
            <input type="text" name="username" id="username" class="form-control" required autofocus>
          </div>
          <div class="form-group">
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
  <!-- Bootstrap JS and dependencies -->
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>