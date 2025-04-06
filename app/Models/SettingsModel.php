<?php

namespace App\Models;

use App\Models\BaseModel;

class SettingsModel extends BaseModel
{
    protected $table = 'settings';
    protected $primaryKey = 'key';
    protected $allowedFields = ['key', 'value'];

    // Singleton instance
    protected static $instance = null;

    /**
     * Trả về instance duy nhất của SettingsModel.
     *
     * @return SettingsModel
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Lấy thông tin cấu hình theo key.
     * Nếu key không tồn tại, lưu giá trị mặc định ($default) vào DB và trả về $default.
     *
     * @param string $key
     * @param mixed $default Giá trị mặc định nếu key không tồn tại
     * @return mixed Giá trị cấu hình hoặc giá trị mặc định nếu không tìm thấy
     */
    public function getByKey($key, $default = null)
    {
        // Sử dụng magic method findOneByKey từ BaseModel
        $setting = $this->findOneByKey($key);
        if ($setting) {
            return $setting['value'];
        } else {
            // Nếu không tìm thấy, lưu giá trị mặc định (nếu có) và trả về giá trị đó
            if ($default !== null) {
                $this->saveConfig($key, $default);
            }
            return $default;
        }
    }

    /**
     * Insert hoặc update cấu hình theo key.
     * Nếu key đã tồn tại thì cập nhật giá trị, nếu chưa tồn tại thì tạo mới.
     *
     * @param string $key
     * @param string $value
     * @return bool|int ID của bản ghi (nếu insert) hoặc true (nếu update thành công), false nếu thất bại.
     */
    public function saveConfig($key, $value)
    {
        $data = ['key' => $key, 'value' => $value];
        $existing = $this->findOneByKey($key);
        if ($existing) {
            // Cập nhật cấu hình nếu đã tồn tại
            return $this->update($key, $data);
        } else {
            // Tạo mới cấu hình
            return $this->insert($data);
        }
    }
}
