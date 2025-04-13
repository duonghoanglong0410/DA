// Hàm focus vào input đầu tiên không bị vô hiệu hóa trong form
function focusFirstInput() {
    setTimeout(function() {
        // Tìm input đầu tiên không bị disabled hoặc readonly
        const $firstInput = $('.autofocus').find('input:not([readonly])').first();
        
        if ($firstInput.length) {
            $firstInput.focus();
        }
    }, 100); // Delay nhỏ để đảm bảo DOM đã được cập nhật
}