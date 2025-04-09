<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class Api extends ResourceController
{
    // Sử dụng định dạng JSON cho các phản hồi
    protected $format = 'json';

    // Khai báo property cho model
    protected $userModel;

    /**
     * Khởi tạo controller theo chuẩn CodeIgniter 4 (không sử dụng __construct)
     *
     * @param \CodeIgniter\HTTP\RequestInterface  $request
     * @param \CodeIgniter\HTTP\ResponseInterface $response
     * @param \Psr\Log\LoggerInterface            $logger
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, 
                                   \CodeIgniter\HTTP\ResponseInterface $response, 
                                   \Psr\Log\LoggerInterface $logger)
    {
        // Gọi initController của parent trước
        parent::initController($request, $response, $logger);
        // Sử dụng import đã khai báo ở đầu file
        $this->userModel = new UserModel();
    }

    /**
     * Lấy gợi ý người dùng dựa trên từ khóa tìm kiếm.
     * Input: GET parameter "q" (từ khóa tìm kiếm)
     * Output: JSON array chứa các người dùng, mỗi người dùng bao gồm id và fullname.
     *
     * Ví dụ: ?q=Nguyễn
     */
    public function getUserSuggestions()
    {
        $q = $this->request->getGet('q');
        if (empty($q)) {
            return $this->respond([]);
        }
        // Tìm kiếm theo fullname hoặc username chứa từ khóa $q
        $users = $this->userModel
                      ->like('fullname', $q)
                      ->orLike('username', $q)
                      ->findAll();

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id'       => $user['id'],
                'fullname' => $user['fullname']
            ];
        }
        return $this->respond($results);
    }

    /**
     * Lấy thông tin người dùng dựa trên danh sách các id.
     * Input: GET parameter "ids" (mảng hoặc chuỗi các id, phân cách bởi dấu phẩy)
     * Output: JSON array chứa thông tin người dùng với id và fullname.
     *
     * Ví dụ: ?ids=1,3,5
     */
    public function getUserByIds()
    {
        $ids = $this->request->getGet('ids');
        if (empty($ids)) {
            return $this->respond([]);
        }
        // Nếu ids không phải là mảng, chuyển đổi chuỗi thành mảng
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        // Lấy thông tin người dùng dựa trên danh sách các id
        $users = $this->userModel->find($ids);
        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id'       => $user['id'],
                'fullname' => $user['fullname']
            ];
        }
        return $this->respond($results);
    }
}
