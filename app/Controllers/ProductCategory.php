<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductCategoryModel;
use App\Models\PurchaseYardGoodsReceiptModel;
use App\Models\TripModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class ProductCategory extends BaseController
{
    protected $productCategoryModel;
    protected $goodsReceiptModel;
    protected $tripModel;

    // Sử dụng initController của CodeIgniter 4 thay vì __construct()
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->productCategoryModel = new ProductCategoryModel();
        $this->goodsReceiptModel    = new PurchaseYardGoodsReceiptModel();
        $this->tripModel            = new TripModel();
    }

    // Mỗi controller phải implement hàm isValidRole, trả về true mặc định
    public function isValidRole($role, $method, $segments)
    {
        return true;
    }

    // GET: Liệt kê danh sách chủng loại mặt hàng (view: getIndex.php)
    public function getIndex()
    {
        $categories = $this->productCategoryModel->findAll();
        $this->assign('categories', $categories);
        return $this->render();
    }

    // GET: Hiển thị form thêm chủng loại (view: getAdd.php)
    public function getAdd()
    {
        $this->assign('name', '');
        $this->assign('abbreviation', '');
        return $this->render();
    }

    // POST: Xử lý thêm chủng loại
    public function postAdd()
    {
        $post = $this->request->getPost();
        $data = [
            'name'          => $post['name'],
            'abbreviation'  => $post['abbreviation'],
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s')
        ];
        $this->productCategoryModel->insert($data);
        return redirect()->to('product-category');
    }

    // GET: Hiển thị form sửa chủng loại (view: getEdit.php)
    public function getEdit($category_id)
    {
        $category = $this->productCategoryModel->find($category_id);
        $this->assign('category_id', $category['id']);
        $this->assign('name', $category['name']);
        $this->assign('abbreviation', $category['abbreviation']);
        return $this->render();
    }

    // POST: Xử lý cập nhật chủng loại
    public function postEdit($category_id)
    {
        $post = $this->request->getPost();
        $data = [
            'name'          => $post['name'],
            'abbreviation'  => $post['abbreviation'],
            'updated_at'    => date('Y-m-d H:i:s')
        ];
        $this->productCategoryModel->update($category_id, $data);
        return redirect()->to('product-category');
    }

    // GET: Xử lý xoá chủng loại mặt hàng
    public function getDelete($category_id)
    {
        // Kiểm tra xem danh mục có xuất hiện trong bảng purchase_yard_goods_receipts không
        $receiptCount = $this->goodsReceiptModel->where('category_id', $category_id)->countAllResults();
        if ($receiptCount > 0) {
            return redirect()->to('product-category')->with('error', 'Không thể xoá danh mục vì đã xuất hiện trong phiếu nhập hàng.');
        }

        // Kiểm tra xem danh mục có xuất hiện trong bảng trips không
        $tripCount = $this->tripModel->where('product_category_id', $category_id)->countAllResults();
        if ($tripCount > 0) {
            return redirect()->to('product-category')->with('error', 'Không thể xoá danh mục vì đã xuất hiện trong chuyến xe.');
        }

        $this->productCategoryModel->delete($category_id);
        return redirect()->to('product-category');
    }
}
