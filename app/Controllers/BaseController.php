<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\UsersModel;
use App\Models\SettingsModel;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    protected $session;
    protected $userModel;
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];
    private $viewData  = [];    //Chứa danh sách các biến sẽ được đưa ra view parser
    private $extraJs  = [];     //Chứa danh sách các file js bổ sung sẽ được thêm vào HTML trả về
    protected $moduleSubFix = "";

    protected $defaultWithHeader = true;    //Có parse kèm header và footer mặc định hay không


    abstract protected function isValidRole($role, $method);
    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {

        parent::initController($request, $response, $logger);

        // Kiểm tra thiết bị người dùng dựa trên User Agent
        $userAgent = $request->getUserAgent();
        if (
            stripos($userAgent, 'Mobile') !== false ||
            stripos($userAgent, 'Android') !== false ||
            stripos($userAgent, 'iPhone') !== false ||
            stripos($userAgent, 'iPad') !== false
        ) {
            // Nếu dùng điện thoại/máy tính bảng
            $this->assign('is_mobile', [[1]]);
            $this->assign('is_desktop', []);
        } else {
            // Nếu dùng máy tính
            $this->assign('is_mobile', []);
            $this->assign('is_desktop', [[1]]);
        }

        $this->session = \Config\Services::session();

        #Tự động chạy migration trong lần đầu tiên user vào hệ thống nếu có thay đổi
        if (empty($this->session->didMigration)) {
            $migration = \Config\Services::migrations();
            try {
                $migration->setNamespace(null)->latest();
            } catch (\Throwable $e) {
            }

            $this->session->didMigration = true;
        }


        $router          = service('router');
        $controller      = strtolower(class_basename($this));
        $method          = $router->methodName();
        $this->userModel = new UsersModel();

        //gắn các biến cơ bản ra view parser
        $this->assign('site_url', site_url());
        $this->assign('base_url', base_url());
        $this->assign('site_title', SettingsModel::getInstance()->getByKey('site_title', "ATVN"));

        if (empty($this->session->userId)) {
            $user = $this->userModel->authenticateByRememberToken(); //Tự động đăng nhập bằng remember token nếu có

            if (empty($user)) {
                //Nếu không tự động đăng nhập được, và nếu user đang vào các trang đăng nhập, báo lỗi thì cho phép
                if ($controller == 'user' && ($method == 'getLogin' || $method == 'postAuthencation' || $method == 'getError')) {
                    return;
                }
                //nếu không thì đá về trang login
                header('Location: ' . site_url("/user/login"));
                exit;
            }

            $this->session->userId = $user['id'];
        } else {
            //Nếu user đã đăng nhập thì lấy thông tin user 
            $user = $this->userModel->find($this->session->userId);
        }

        //Ghi nhận các giá trị cơ bản
        $this->assign('userFullName', $user['fullname']);

        if (empty($user) || !$this->userModel->hasAccess($this->session->userId, $controller, $method) || !$this->isValidRole($this->session->userRole, $method))
        {
            header('Location: ' . site_url("/user/error"));
            exit;
        }

        $permissionsMapping = $this->userModel->getUserRolePermissionsMapping($this->session->userId);
        foreach ($permissionsMapping as $key => $value) {
            $this->assign($key, $value);
        }

    }

    //Thêm JS đặc thù cho trang
    protected function importJs($uri)
    {
        $this->extraJs[] = ['js_url' => $uri];
    }

    //Gán giá trị vào biến sẽ đưa ra view parser
    protected function assign($key, $value)
    {
        $this->viewData[$key] = $value;
    }

    //Nối thêm giá trị mới vào sau biến sẽ đưa ra view parser
    protected function assignAppend($key, $value)
    {
        if (empty($this->viewData[$key])) {
            $this->assign($key, $value);
        } else {
            $this->viewData[$key] .= $value;
        }
    }

    //Parse view và trả về nội dung
    protected function render($viewname = '', $withHeader = true, $withFooter = true)
    {
        $parser     = \Config\Services::parser();
        $content    = "";
        $router     = service('router');
        $controller = strtolower(class_basename($this));

        if (empty($viewname)) {
            $viewname = $controller . "/" . $router->methodName();
        }

        $this->viewData['extrajs'] = $this->extraJs;

        $parser = $parser->setData($this->viewData);

        if ($withHeader && $this->defaultWithHeader) {
            $content .= $parser->render("modules/header{$this->moduleSubFix}");
        }

        $content .= $parser->render("$viewname.php");

        if ($withFooter && $this->defaultWithHeader) {
            $content .= $parser->render("modules/footer{$this->moduleSubFix}");
        }

        return $content;
    }
}
