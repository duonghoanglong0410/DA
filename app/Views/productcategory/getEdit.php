<div class="container mt-3">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="form-box">
        <h2 class="mb-4">Sửa chủng loại mặt hàng</h2>
        <form action="{site_url}product-category/edit/{id}" method="post" class="getDetail">
          <div class="mb-3">
            <label for="name" class="form-label">Tên mặt hàng</label>
            <input type="text" name="name" id="name" class="form-control" value="{name}" required>
          </div>
          <div class="mb-3">
            <label for="abbreviation" class="form-label">Viết tắt</label>
            <input type="text" name="abbreviation" id="abbreviation" class="form-control" value="{abbreviation}" required>
          </div>
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
function removeAccents(str) {
  // Sử dụng chuẩn Unicode Normalization để loại bỏ dấu
  var newStr = str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
  // Bổ sung chuyển đổi "đ" và "Đ"
  newStr = newStr.replace(/đ/g, "d").replace(/Đ/g, "D");
  return newStr;
}

$(document).ready(function(){
  $("#name").on("input", function(){
    var fullName = $(this).val().trim();
    var abbr = "";
    if(fullName.length === 0){
      $("#abbreviation").val("");
      return;
    }
    // Tách tên theo khoảng trắng (loại bỏ khoảng trắng thừa)
    var words = fullName.split(/\s+/);
    if(words.length === 1){
      // Nếu chỉ có 1 từ: lấy 3 ký tự đầu của từ đó
      abbr = words[0].substring(0,3);
    } else if(words.length === 2){
      // Nếu có 2 từ: lấy 2 ký tự đầu của từ thứ nhất nối với ký tự đầu của từ thứ hai
      abbr = words[0].substring(0,2) + words[1].substring(0,1);
    } else {
      // Nếu có 3 từ trở lên: lấy ký tự đầu của 3 từ đầu tiên
      abbr = words[0].substring(0,1) + words[1].substring(0,1) + words[2].substring(0,1);
    }
    abbr = removeAccents(abbr).toUpperCase();
    $("#abbreviation").val(abbr);
  });
});
</script>
