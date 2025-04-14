<?php

namespace App\Controllers;

use ReflectionClass;
use ReflectionMethod;

class Home extends BaseController
{
    protected function isValidRole($role, $method, $segments)
    {
        return true;
    }

    public function index(): string
    {
        $userId = $this->session->userId;
        // Lấy danh sách menu từ model (không dùng lệnh kết nối DB trực tiếp trong controller)
        // $menus = $this->userModel->getUserMainMenu($userId);
        $menus = [
            [
                'name' => 'Dashboard',
                'icon' => 'fa-solid fa-house',
                'url' => '/dashboard',
                'children' => []
            ],
            [
                'name' => 'Quản lý phiếu',
                'icon' => 'fa-solid fa-file-invoice',
                'url' => '/voucher',
                'children' => []
            ],
            [
                'name' => 'Quản lý hàng hóa',
                'icon' => 'fa-solid fa-boxes-stacked',
                'url' => '/goods',
                'children' => []
            ],
            [
                'name' => 'Quản lý khách hàng',
                'icon' => 'fa-solid fa-users',
                'url' => '/buyer',
                'children' => []
            ],
        ];
        $this->assign('menus', $menus);
        return $this->render();
    }

    public function getScan():string
    {
        // Đường dẫn thư mục gốc của app
        $baseDir     = APPPATH; // "app/"
        $controllerDir = $baseDir . 'Controllers';
        $modelDir      = $baseDir . 'Models';
        $viewDir       = $baseDir . 'Views';

        /**
         * Hàm quét thư mục theo đệ quy với độ sâu tối đa.
        *
        * @param string $dir Thư mục cần quét.
        * @param int $maxDepth Độ sâu tối đa.
        * @param int $currentDepth Độ sâu hiện tại.
        * @return array Mảng chứa cấu trúc file và thư mục.
        */
        $scanDirectory = function($dir, $maxDepth = 2, $currentDepth = 0) use (&$scanDirectory) {
            $results = [];
            if (!is_dir($dir)) {
                return $results;
            }
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    $results[$file] = $scanDirectory($path, $maxDepth, $currentDepth + 1);
                } else {
                    $results[] = $file;
                }
            }
            return $results;
        };

        /**
         * Hàm lấy danh sách các file PHP trong thư mục với độ sâu tối đa.
        *
        * @param string $dir
        * @param int $maxDepth
        * @param int $currentDepth
        * @return array
        */
        $getPhpFiles = function($dir, $maxDepth = 2, $currentDepth = 0) use (&$getPhpFiles) {
            $filesList = [];
            if (!is_dir($dir)) {
                return $filesList;
            }
            $entries = scandir($dir);
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $entry;
                if (is_dir($path) && $currentDepth < $maxDepth - 1) {
                    $filesList = array_merge($filesList, $getPhpFiles($path, $maxDepth, $currentDepth + 1));
                } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                    $filesList[] = $path;
                }
            }
            return $filesList;
        };

        /**
         * Hàm lấy danh sách public method của 1 lớp theo Reflection.
        *
        * @param string $className Tên lớp (đã bao gồm namespace nếu cần).
        * @return array Danh sách tên method.
        */
        $getPublicMethods = function($className) {
            $methods = [];
            try {
                $reflector = new ReflectionClass($className);
                foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    // Chỉ lấy các method được khai báo trong chính class đó
                    if ($method->getDeclaringClass()->getName() === $className) {
                        $methods[] = $method->getName();
                    }
                }
            } catch (\ReflectionException $e) {
                return $methods;
            }
            return $methods;
        };

        /**
         * Hàm chuyển đường dẫn file thành tên lớp (giả định tên file trùng với tên lớp, không có phần mở rộng).
        *
        * @param string $filePath
        * @return string Tên lớp.
        */
        $getClassNameFromFile = function($filePath) {
            return pathinfo($filePath, PATHINFO_FILENAME);
        };

        // --------- QUÉT CONTROLLERS ----------
        $controllerFiles = $getPhpFiles($controllerDir, 2);
        $controllers = [];
        foreach ($controllerFiles as $file) {
            require_once $file;
            $className    = $getClassNameFromFile($file);
            $fullClassName = "App\\Controllers\\" . $className;
            if (class_exists($fullClassName)) {
                $methods = $getPublicMethods($fullClassName);
                $controllers[$className] = $methods;
            }
        }

        // --------- QUÉT MODELS ----------
        $modelFiles = $getPhpFiles($modelDir, 2);
        $models = [];
        foreach ($modelFiles as $file) {
            require_once $file;
            $className = $getClassNameFromFile($file);
            $fullClassName = "App\\Models\\" . $className;
            if (class_exists($fullClassName)) {
                $methods = $getPublicMethods($fullClassName);
                $models[$className] = $methods;
            }
        }

        // --------- QUÉT VIEWS ----------
        $views = $scanDirectory($viewDir, 2);

        $output  = "Hãy ghi nhớ danh sách cấu trúc của dự án hiện tại";

        // --------- TẠO OUTPUT HTML ----------
        $output  .= "<h1>Danh sách Controller</h1>";
        foreach ($controllers as $cname => $methods) {
            $output .= "<h2>- $cname:</h2>";
            if (count($methods) > 0) {
                $output .= "<ul>";
                foreach ($methods as $method) {
                    $output .= "<li>$method</li>";
                }
                $output .= "</ul>";
            } else {
                $output .= "<p>(Không có public method nào)</p>";
            }
        }

        $output .= "<h1>Danh sách Model</h1>";
        foreach ($models as $mname => $methods) {
            $output .= "<h2>- $mname:</h2>";
            if (count($methods) > 0) {
                $output .= "<ul>";
                foreach ($methods as $method) {
                    $output .= "<li>$method</li>";
                }
                $output .= "</ul>";
            } else {
                $output .= "<p>(Không có public method nào)</p>";
            }
        }

        /**
         * Hàm in cấu trúc thư mục của Views dưới dạng HTML.
        *
        * @param array $structure
        * @param int $indent
        * @return string HTML
        */
        $printViewStructure = function($structure, $indent = 0) use (&$printViewStructure) {
            $prefix = str_repeat("&nbsp;&nbsp;&nbsp;", $indent);
            $html   = "<ul>";
            foreach ($structure as $key => $value) {
                if (is_array($value)) {
                    $html .= "<li>" . $prefix . "<strong>$key:</strong>" . $printViewStructure($value, $indent + 1) . "</li>";
                } else {
                    $html .= "<li>" . $prefix . $value . "</li>";
                }
            }
            $html .= "</ul>";
            return $html;
        };

        $output .= "<h1>Danh sách View</h1>";
        $output .= $printViewStructure($views);

        // Gán biến ra view thông qua phương thức assign
        return $output;
    }
}
