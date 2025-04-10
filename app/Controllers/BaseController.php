<?php

namespace App\Controllers;

use App\Constants\Constants;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\UserModel;
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
    protected $defaultGlobalMess = true; 


    abstract protected function isValidRole($role, $method, $segments);
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
        if (empty($this->session->didMigration) || ENVIRONMENT === 'development')
        {
            
            $migration = \Config\Services::migrations();
            // try {
                $migration->setNamespace(null)->latest();
            // } catch (\Throwable $e) {
            // }

            $this->session->didMigration = true;
        }


        $router          = service('router');
        $controller      = strtolower(class_basename($this));
        $method          = $router->methodName();
        $this->userModel = new UserModel();

        //gắn các biến cơ bản ra view parser
        $this->initFlashData();
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

        if (
            empty($user) 
            || !$this->userModel->hasAccess($this->session->userId, $controller, $method) 
            || !$this->isValidRole($this->session->userRole, $method, $request->getUri()->getSegments())
            )
        {
            if (ENVIRONMENT === 'development') {
                $this->session->setFlashdata('error', "Truy cập đến controller <b>$controller</b> method <b>$method</b> bị chặn, hãy kiểm tra phân quyền hoặc xem xét table <b>permissions</b> cột <b>action</b> và </b>method</b>");
            }
            header('Location: ' . site_url("/user/error"));
            exit;
        }

        $permissionsMapping = $this->userModel->getUserRolePermissionsMapping($this->session->userId);
        foreach ($permissionsMapping as $key => $value) {
            $this->assign($key, $value);
        }

    }


    protected function globalMessVisibility($visible)
    {
        $this->defaultGlobalMess = $visible;
    }

    private function initFlashData()
    {
        // Lấy flashdata error và success
        $error = $this->session->getFlashdata('error');
        if (empty($error)) {
            $this->assign('error', []);
        } else {
            $this->assign('error', [['mess' => $error]]);
        }
        $success = $this->session->getFlashdata('success');
        if (empty($success)) {
            $this->assign('success', []);
        } else {
            $this->assign('success', [['mess' => $success]]);
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

        if ($this->defaultGlobalMess)
        {
            $this->viewData['globalmess'] = '';
        }
        else
        {
            $this->viewData['globalmess'] = 'none';
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

    /**
     * Xử lý phân trang và gán các biến cần thiết cho view
     * 
     * @param object $controller Controller hiện tại
     * @param int $totalRecords Tổng số bản ghi
     * @return void
     */
    public function handlePagination($totalRecords)
    {
        // Lấy trang hiện tại và số bản ghi trên mỗi trang từ query string
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? Constants::DEFAULT_PER_PAGE;
        
        // Validate per_page value
        if (!in_array($perPage, Constants::PER_PAGE_OPTIONS)) {
            $perPage = Constants::DEFAULT_PER_PAGE;
        }

        // Tính tổng số trang
        $totalPages = ceil($totalRecords / $perPage);

        // Prepare pagination data
        $pagination = [];
        
        // Add previous button
        $pagination[] = [
            'display' => '«',
            'url' => '?page=' . ($page - 1) . '&per_page=' . $perPage,
            'is_active' => false,
            'is_disabled' => $page <= 1
        ];
        
        // Add page numbers
        for ($i = 1; $i <= $totalPages; $i++) {
            $pagination[] = [
                'display' => $i,
                'url' => '?page=' . $i . '&per_page=' . $perPage,
                'is_active' => $i == $page,
                'is_disabled' => false
            ];
        }
        
        // Add next button
        $pagination[] = [
            'display' => '»',
            'url' => '?page=' . ($page + 1) . '&per_page=' . $perPage,
            'is_active' => false,
            'is_disabled' => $page >= $totalPages
        ];

        // Prepare per page options
        $perPageOptions = [];
        foreach (Constants::PER_PAGE_OPTIONS as $option) {
            $perPageOptions[] = [
                'value' => $option,
                'is_selected' => $option == $perPage,
                'url' => '?page=1&per_page=' . $option
            ];
        }

        
        // Gán các biến cho view
        $this->assign('pagination_links', $pagination);
        $this->assign('per_page_options', $perPageOptions);
        $this->assign('current_per_page', $perPage);

        $this->assign('pagination', $this->render("modules/pagination"));
        
        // Trả về page và perPage để controller có thể sử dụng
        return [
            'page' => $page,
            'perPage' => $perPage
        ];
    }    
}
