<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="mb-4">Chỉnh sửa phân quyền cho người dùng: {user_username}</h2>
        
        <form action="{site_url}user-permission/edit/{user_id}" method="post">
          
          <h4>Chức năng theo bãi</h4>
          {group1}
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" id="role-{id}" value="{id}" {checked}>
            <label class="form-check-label" for="role-{id}">{name}</label>
          </div>
          {!dropdown!}
          {/group1}
          
          <h4>Quỹ tiền tệ</h4>
          {group2}
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" id="role-{id}" value="{id}" {checked}>
            <label class="form-check-label" for="role-{id}">{name}</label>
          </div>
          {!dropdown!}
          {/group2}
          
          <h4>Chức năng chung</h4>
          {group3}
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="roles[]" id="role-{id}" value="{id}" {checked}>
            <label class="form-check-label" for="role-{id}">{name}</label>
          </div>
          {/group3}
          
          <button type="submit" class="btn btn-primary mt-3">Cập nhật phân quyền</button>
        </form>
      </div>
    </div>
  </div>
</div>
