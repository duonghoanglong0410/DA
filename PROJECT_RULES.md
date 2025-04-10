# Quy tắc và Hướng dẫn Dự án

## Quy tắc Chung
- Sử dụng framework CodeIgniter 4.
- Nội dung giao diện và thông báo lỗi phải bằng tiếng Việt có dấu, trong khi tên biến, hằng số, tên hàm và các định danh khác phải bằng tiếng Anh.
- URL đến index của một controller không có tham số nên bỏ 'index' trong URL. Ví dụ, `return redirect()->to('yard/index');` là không đúng, đúng phải là `return redirect()->to('yard');`.
- Viết thông báo, văn bản và ghi chú bằng tiếng Việt có dấu.
- Trong tiếng Việt, sử dụng dấu trọng âm như oà, uý, oè, thay vì òa, òe.
- Trong bất kỳ URL nào đến một phương thức, loại bỏ loại phương thức (get/post/delete/...) khỏi tên phương thức. Ví dụ, controller `yard` với phương thức `getUpdate` nên có liên kết tương ứng là `yard/update`.
- Trong bất kỳ URL nào, các từ phân biệt bằng chữ viết hoa nên được thay thế bằng dấu gạch ngang. Ví dụ, phương thức API controller `getUserSuggestions` nên có URL là `api/user-suggestions`.
- Tất cả các truy vấn cơ sở dữ liệu được thực hiện qua model.
- Nếu đường dẫn đến index của một controller không có tham số, bỏ `/index`. Ví dụ, `action="{site_url}yard/index"` nên là `action="{site_url}yard"`.
- Trong controller, lệnh redirect không sử dụng `{site_url}`; trong view, liên kết cần sử dụng `{site_url}`, và không thêm dấu gạch chéo sau `{site_url}`.
- Sử dụng import, không ghi trực tiếp namespace khi sử dụng.
- Nếu sử dụng thuộc tính trong một class (controller, model, ...), khai báo nó ở đầu class trước khi sử dụng.

## Quy định về Controller và Model
- Tất cả các model phải kế thừa từ `BaseModel`.
- Để lấy user ID trong controller, sử dụng `$this->session->userId`.
- Các truy vấn dữ liệu phải luôn được thực hiện qua model. Không được truy vấn cơ sở dữ liệu trực tiếp từ controller.
- Không sử dụng `\Config\Database::connect()` trong controller hoặc bất kỳ phương thức tương tự nào; phải thực hiện qua model.
- Mỗi controller phải triển khai hàm `isValidRole($role, $method, $segments)` (mặc định trả về true).
- Controller không được sử dụng `public function __construct()` mà phải sử dụng `initController` của CodeIgniter 4.
- Để trả về view, chỉ cần gọi `return $this->render();` mà không cần truyền tham số.
- Để gán biến ra view, sử dụng `$this->assign('tên biến', $data);`.
- Khi cần hiển thị giá trị thuộc tính bên trong một array, `$this->assign` cần phải gán giá trị của từng key bên trong, không thể gán toàn bộ array trong một lệnh.
- Không được gán mã HTML vào biến bằng lệnh `$this->assign`; nếu cần hãy dùng các phương pháp ẩn/hiện ở trên.

## Quy định về View
- Tất cả các label cho input phải chỉ định `label-for` để khi bấm vào text các input tương ứng sẽ được click.
- Trong JavaScript, nếu có lệnh `if` sau `{`, sẽ gây lỗi, vì vậy hãy chèn thêm ghi chú giữa chúng.
- Các view sử dụng cú pháp parser với biến đơn: `{yard_id}`, `{yard_code}`, `{yard_name}`, `{currencies}`, `{cfCurrencies}`, v.v.
- Không được dùng `{if` trong view; nếu cần hiển thị/ẩn gì đó, hãy dùng biến array.
- Cách khác để ẩn/hiện một thứ là dùng thuộc tính display của CSS; gán một biến giá trị rỗng (hiện) hoặc none (ẩn) để thay đổi giá trị của display cho một thẻ nào đó.
- Trước khi chèn liên kết JS script hay CSS vào view, hãy kiểm tra file `header.php` và `footer.php` xem đã có chưa; nếu chưa có thì chèn vào `header` hoặc `footer.php`, nếu có rồi thì không chèn thêm nữa.
- Các đường link (href, form action) trong view có tiền tố `{site_url}`.
- Các view có nút "Trở lại" sử dụng `onclick="window.history.back();`.
- Các form phải dùng class `form-box getDetail`, sử dụng các lớp Bootstrap để căn lề cho các cột.
- File `header.php` và `footer.php` luôn luôn được tự động thêm vào khi render view, vì vậy hãy loại bỏ hoàn toàn phần đầu và cuối trang nếu có, chỉ cần xây dựng code của trang.
- Tên file view phải trùng với tên của method, ví dụ method là `getAdd` thì tên view phải là `getAdd`, hay method là `postEdit` thì tên view phải là `postEdit`.
- Các cửa sổ nhập liệu cần giới hạn chiều rộng trên desktop, full width trên mobile. Nên dùng theo chuẩn sau:
  ```html
  <div class="container mt-3">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="form-box">
          <h2 class="mb-4"> <đây là tiêu đề của form> </h2>
          <!-- Nội dung nằm trong này -->
          <div class="mt-3 d-flex justify-content-between">
            <!-- Danh sách các nút chức năng nằm ở đây -->
            <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
            <a class="btn btn-secondary" href='<trang trang chính của controller ví dụ như {site_url}cash-fund>'>Huỷ</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  ```
- Mặc định các con số hiển thị theo định dạng Việt Nam: không có phần lẻ thập phân, phân cách phần ngàn bằng dấu . đồng thời nếu đây là một giá trị hiển thị trong một cột của table thì cần căn lề phải.
- Mặc định ngày tháng hiển thị theo định dạng Việt Nam: dd-mm-yyyy.
- Lưu ý khi dùng array để ẩn hiện là bên trong nếu truy xuất lại thuộc tính của item cấp cao hơn trước đó sẽ bị lỗi. Ví dụ, bên trong `can_delete` có lấy `{id}` thì sẽ gây lỗi. Trường hợp này hãy chuyển qua dùng thay đổi display nếu được, hoặc dùng cách sau:
  1. Trên view bổ sung thêm biến `can_delete_id`.
  2. Trong controller bổ sung thêm lệnh gán như sau cho từng dòng: `followups[$key]['can_delete'] = [['can_delete_id' => followups[$key]['id']]];`

## Hướng dẫn về Migration
- Luôn bổ sung ghi chú bằng tiếng Việt có dấu cho mỗi cột.
- Luôn hướng dẫn cách bổ sung cập nhật lại ER diagram; nếu chưa upload, hãy nhắc nhở upload.
- Gợi ý tên file migration đảm bảo quy tắc sau: `<năm hiện tại>-<tháng hiện tại>-<ngày hiện tại>-<6 chữ số tăng dần không trùng>_<tên gợi nhớ liên quan đến hành động và table>.php`
- Ví dụ: Ngày 8 tháng 4 năm 2025, cho yêu cầu thứ 17 thêm cột `warehouse_id` vào bảng `user_role_assignments`, tên file migration nên là: `2025-04-08-000017_AddWarehouseIdToUserRoleAssignments.php`.

Những quy tắc này phải được ghi nhớ và áp dụng cho toàn bộ dự án. 