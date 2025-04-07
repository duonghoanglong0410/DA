
<div class="container mt-3">
  <h2 class="mb-4">Chỉnh sửa phân quyền cho người dùng: {user.username}</h2>
  <form action="{site_url}userPermission/edit/{user.id}" method="post">
    <div class="form-group">
      <label>Chọn các role:</label>
      {roles}
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="roles[]" value="{id}" {checked}>
        <label class="form-check-label">{name}</label>
      </div>
      {/roles}
    </div>
    <button type="submit" class="btn btn-primary mt-3">Cập nhật phân quyền</button>
  </form>
</div>
