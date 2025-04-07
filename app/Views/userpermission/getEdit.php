<div class="container mt-3">
  <div class="form-box">
    <h2 class="mb-4">Chỉnh sửa phân quyền cho người dùng: {user_username}</h2>
    <form action="{site_url}user-permission/edit/{user_id}" method="post">
      
      <h4>Chức năng theo bãi</h4>
      {group1}
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="roles[]" id="role-{id}" value="{id}" {checked}>
          <label class="form-check-label" for="role-{id}">{name}</label>
        </div>
        <div class="mt-2">
          {toggle}
            <label class="btn btn-outline-primary {toggle_active}">
              <input type="checkbox" name="target_{toggle_role_id}[]" value="{toggle_y_id}" autocomplete="off" {toggle_checked}> {toggle_yard_name}
            </label>
          {/toggle}
        </div>
      </div>
      {/group1}
      
      <h4>Quỹ tiền tệ</h4>
      {group2}
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="roles[]" id="role-{id}" value="{id}" {checked}>
          <label class="form-check-label" for="role-{id}">{name}</label>
        </div>
        <div class="mt-2">
          {toggle}
            <label class="btn btn-outline-primary {toggle_active}">
              <input type="checkbox" name="target_{toggle_role_id}[]" value="{toggle_y_id}" autocomplete="off" {toggle_checked}> {toggle_yard_name}
            </label>
          {/toggle}
        </div>
      </div>
      {/group2}
      
      <h4>Chức năng chung</h4>
      {group3}
      <div class="mb-3">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="roles[]" id="role-{id}" value="{id}" {checked}>
          <label class="form-check-label" for="role-{id}">{name}</label>
        </div>
      </div>
      {/group3}
      
      <div class="mt-3 d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Trở lại</button>
          </div>

    </form>
  </div>
</div>

<script>
$(document).ready(function(){
    // Khi bất kỳ checkbox nào có name bắt đầu bằng "target_" thay đổi trạng thái, tự động đánh dấu checkbox role tương ứng.
    $('input[name^="target_"]').on('change', function(){
        // Lấy thuộc tính name, ví dụ: "target_3[]"
        var nameAttr = $(this).attr('name'); 
        // Lấy role id bằng cách loại bỏ "target_" và "[]"
        var roleId = nameAttr.replace('target_', '').replace('[]', '');
        // Đánh dấu checkbox của role đó
        $('#role-' + roleId).prop('checked', true);
    });
});
</script>
