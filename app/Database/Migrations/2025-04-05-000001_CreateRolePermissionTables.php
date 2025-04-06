<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
Bảng roles:

Lưu danh sách các nhóm quyền (role) theo file Excel tối giản.

Bảng permissions:

Lưu danh sách chức năng (permission) với các cột bổ sung action và method phục vụ ACL.

Cột group được sử dụng để liên kết mỗi chức năng với nhóm quyền tương ứng.

Bảng role_permission:

Bảng pivot liên kết giữa roles và permissions.
*/
class CreateAclTablesFromExcel extends Migration
{
    public function up()
    {
        // 1. Tạo bảng roles
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name'        => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at'  => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at'  => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('roles');

        // 2. Tạo bảng permissions với cột action và method cho ACL
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name'        => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // Cột group để liên kết chức năng với nhóm quyền
            'group'       => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'action'      => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'method'      => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            'created_at'  => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at'  => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('permissions');

        // 3. Tạo bảng role_permission (bảng pivot)
        $this->forge->addField([
            'role_id'       => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'permission_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
        ]);
        $this->forge->addKey(['role_id', 'permission_id'], true);
        $this->forge->createTable('role_permission');

        // --------------------------------------------
        // Insert dữ liệu khởi tạo
        // --------------------------------------------
        $db = \Config\Database::connect();

        // a. Insert các nhóm quyền (roles)
        $rolesData = [
            ['name' => 'Nhân viên bãi', 'description' => 'Quyền cho nhân viên bãi'],
            ['name' => 'Thủ quỹ của bãi', 'description' => 'Quyền cho thủ quỹ của bãi'],
            ['name' => 'Điều phối xe', 'description' => 'Quyền cho điều phối xe'],
            ['name' => 'Kiểm soát tài chính kho bãi', 'description' => 'Quyền cho kiểm soát tài chính kho bãi'],
            ['name' => 'Quản lý kho', 'description' => 'Quyền cho quản lý kho'],
            ['name' => 'Kế toán công nợ', 'description' => 'Quyền cho kế toán công nợ'],
            ['name' => 'Thủ quỹ', 'description' => 'Quyền cho thủ quỹ'],
            ['name' => 'Quản lý bán hàng', 'description' => 'Quyền cho quản lý bán hàng'],
            ['name' => 'Phụ trách chi phí hải quan', 'description' => 'Quyền cho phụ trách chi phí hải quan'],
            ['name' => 'Quản lý chi phí hải quan', 'description' => 'Quyền cho quản lý chi phí hải quan'],
            ['name' => 'Quản lý hệ thống', 'description' => 'Quyền cho quản lý hệ thống'],
        ];
        foreach ($rolesData as $role) {
            $db->table('roles')->insert($role);
        }

        // b. Insert danh sách chức năng (permissions)
        // Các dữ liệu dưới đây được chuyển từ file Excel tối giản,
        // với mỗi bản ghi gồm: group, name, description, action, method.
        $permissionsData = [
            // Nhân viên bãi
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Màn hình gợi ý xe đã cân NHẬP trong ngày',
                'description' => 'Hiển thị danh sách xe đã cân NHẬP trong ngày để hỗ trợ thu mua',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Màn hình nhập hàng',
                'description' => 'Cho phép nhập hàng và tạo phiếu cân đầu vào',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Lựa chọn loại khách hàng',
                'description' => 'Chọn loại khách hàng cho giao dịch xuất bán',
                'action'      => 'select',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Màn hình gợi ý danh sách xe đã cân XUẤT trong ngày',
                'description' => 'Hiển thị danh sách xe đã cân XUẤT trong ngày',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Xuất bán, cân đầu ra: Lựa chọn xe tải',
                'description' => 'Chọn xe tải cho giao dịch xuất bán',
                'action'      => 'select',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Xuất bán, cân đầu ra: Màn hình xuất cân đầu ra',
                'description' => 'Hiển thị dữ liệu cân đầu ra khi xuất bán',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Danh sách phiếu cân đầu vào',
                'description' => 'Hiển thị danh sách phiếu cân đầu vào',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Danh sách phiếu cân đầu ra',
                'description' => 'Hiển thị danh sách phiếu cân đầu ra',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Lập phiếu đề nghị điều chỉnh phiếu cân thu mua',
                'description' => 'Tạo phiếu đề nghị điều chỉnh phiếu cân cho thu mua',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Lập phiếu đề nghị điều chỉnh phiếu cân xuất bán',
                'description' => 'Tạo phiếu đề nghị điều chỉnh phiếu cân cho xuất bán',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Danh sách đề nghị điều chỉnh phiếu cân xuất bán',
                'description' => 'Hiển thị danh sách phiếu đề nghị điều chỉnh phiếu cân xuất bán',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Điều chỉnh tồn kho tại bãi',
                'description' => 'Cập nhật số liệu tồn kho tại bãi',
                'action'      => 'update',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Nhân viên bãi',
                'name'        => 'Màn hình thông tin kho bãi',
                'description' => 'Hiển thị thông tin chi tiết của kho bãi',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            // Thủ quỹ của bãi
            [
                'group'       => 'Thủ quỹ của bãi',
                'name'        => 'Lập phiếu chi ngoài',
                'description' => 'Tạo phiếu chi ngoài',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Thủ quỹ của bãi',
                'name'        => 'Lập phiếu chi nội bộ',
                'description' => 'Tạo phiếu chi nội bộ',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Thủ quỹ của bãi',
                'name'        => 'Lập phiếu đổi tiền',
                'description' => 'Tạo phiếu đổi tiền',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Thủ quỹ của bãi',
                'name'        => 'Danh sách phiếu nhận tiền',
                'description' => 'Hiển thị danh sách phiếu nhận tiền',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Thủ quỹ của bãi',
                'name'        => 'Danh sách phiếu thu/chi',
                'description' => 'Hiển thị danh sách phiếu thu/chi',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Thủ quỹ của bãi',
                'name'        => 'Danh sách phiếu đổi tiền',
                'description' => 'Hiển thị danh sách phiếu đổi tiền',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Thủ quỹ của bãi',
                'name'        => 'Chi tiết xác nhận phiếu nhận tiền',
                'description' => 'Hiển thị chi tiết xác nhận phiếu nhận tiền',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Thủ quỹ của bãi',
                'name'        => 'Màn hình thông tin kho bãi',
                'description' => 'Hiển thị thông tin kho bãi',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            // Điều phối xe
            [
                'group'       => 'Điều phối xe',
                'name'        => 'Lập phiếu bán hàng',
                'description' => 'Tạo phiếu bán hàng',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Điều phối xe',
                'name'        => 'Tồn kho tại bãi',
                'description' => 'Hiển thị tồn kho tại bãi',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Điều phối xe',
                'name'        => 'Điều chỉnh tồn kho tại bãi',
                'description' => 'Cập nhật tồn kho tại bãi',
                'action'      => 'update',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Điều phối xe',
                'name'        => 'Theo dõi xe đang vận chuyển',
                'description' => 'Hiển thị xe đang vận chuyển',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Điều phối xe',
                'name'        => 'Danh sách phiếu bán hàng',
                'description' => 'Hiển thị danh sách phiếu bán hàng',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Điều phối xe',
                'name'        => 'Lập phiếu đề nghị điều chỉnh phiếu cân xuất bán',
                'description' => 'Tạo phiếu đề nghị điều chỉnh phiếu cân cho xuất bán',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Điều phối xe',
                'name'        => 'Danh sách đề nghị điều chỉnh phiếu cân',
                'description' => 'Hiển thị danh sách đề nghị điều chỉnh phiếu cân',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Điều phối xe',
                'name'        => 'Danh sách phiếu cân tự động',
                'description' => 'Hiển thị danh sách phiếu cân tự động',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            // Kiểm soát tài chính kho bãi
            [
                'group'       => 'Kiểm soát tài chính kho bãi',
                'name'        => 'Danh sách phiếu chi ngoài',
                'description' => 'Hiển thị danh sách phiếu chi ngoài',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Kiểm soát tài chính kho bãi',
                'name'        => 'Danh sách phiếu thu tiền',
                'description' => 'Hiển thị danh sách phiếu thu tiền',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            // Quản lý kho
            [
                'group'       => 'Quản lý kho',
                'name'        => 'Danh sách xe nhập',
                'description' => 'Hiển thị danh sách xe nhập',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý kho',
                'name'        => 'Xuất bán, cân đầu ra: Lựa chọn xe tải',
                'description' => 'Chọn xe tải cho giao dịch xuất bán, cân đầu ra',
                'action'      => 'select',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý kho',
                'name'        => 'Xuất bán, cân đầu ra: Màn hình xuất cân đầu ra',
                'description' => 'Hiển thị màn hình xuất cân đầu ra',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý kho',
                'name'        => 'Danh sách phiếu cân đầu ra',
                'description' => 'Hiển thị danh sách phiếu cân đầu ra',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý kho',
                'name'        => 'Lập phiếu đề nghị điều chỉnh phiếu cân xuất bán',
                'description' => 'Tạo phiếu đề nghị điều chỉnh phiếu cân cho xuất bán',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Quản lý kho',
                'name'        => 'Danh sách đề nghị điều chỉnh phiếu cân xuất bán',
                'description' => 'Hiển thị danh sách đề nghị điều chỉnh phiếu cân xuất bán',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý kho',
                'name'        => 'Điều chỉnh tồn kho tại bãi',
                'description' => 'Cập nhật tồn kho tại bãi',
                'action'      => 'update',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Quản lý kho',
                'name'        => 'Màn hình thông tin kho bãi',
                'description' => 'Hiển thị thông tin kho bãi',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            // Kế toán công nợ
            [
                'group'       => 'Kế toán công nợ',
                'name'        => 'Danh sách công nợ khách hàng phải thu',
                'description' => 'Hiển thị danh sách công nợ của khách hàng phải thu',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Kế toán công nợ',
                'name'        => 'Chi tiết danh sách công nợ khách hàng phải thu',
                'description' => 'Hiển thị chi tiết công nợ của khách hàng phải thu',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Kế toán công nợ',
                'name'        => 'Lập phiếu thu tạm ứng khách hàng',
                'description' => 'Tạo phiếu thu tạm ứng cho khách hàng',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Kế toán công nợ',
                'name'        => 'Lập phiếu thu theo đơn hàng',
                'description' => 'Tạo phiếu thu theo đơn hàng',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Kế toán công nợ',
                'name'        => 'Danh sách công nợ phải trả',
                'description' => 'Hiển thị danh sách công nợ phải trả',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Kế toán công nợ',
                'name'        => 'Lập phiếu chi tiền thanh toán công nợ theo xe hàng',
                'description' => 'Tạo phiếu chi thanh toán công nợ theo xe hàng',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Kế toán công nợ',
                'name'        => 'Lập phiếu chi ngoài',
                'description' => 'Tạo phiếu chi ngoài',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            // Thủ quỹ
            [
                'group'       => 'Thủ quỹ',
                'name'        => 'Lập phiếu chi ngoài',
                'description' => 'Tạo phiếu chi ngoài',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Thủ quỹ',
                'name'        => 'Lập phiếu chi nội bộ',
                'description' => 'Tạo phiếu chi nội bộ',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Thủ quỹ',
                'name'        => 'Lập phiếu đổi tiền',
                'description' => 'Tạo phiếu đổi tiền',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Thủ quỹ',
                'name'        => 'Danh sách phiếu nhận tiền',
                'description' => 'Hiển thị danh sách phiếu nhận tiền',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Thủ quỹ',
                'name'        => 'Danh sách phiếu thu/chi',
                'description' => 'Hiển thị danh sách phiếu thu/chi',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Thủ quỹ',
                'name'        => 'Danh sách phiếu đổi tiền',
                'description' => 'Hiển thị danh sách phiếu đổi tiền',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Thủ quỹ',
                'name'        => 'Chi tiết xác nhận phiếu nhận tiền',
                'description' => 'Hiển thị chi tiết xác nhận phiếu nhận tiền',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Thủ quỹ',
                'name'        => 'Báo cáo tồn quỹ',
                'description' => 'Hiển thị báo cáo tồn quỹ',
                'action'      => 'report',
                'method'      => 'GET'
            ],
            // Quản lý bán hàng
            [
                'group'       => 'Quản lý bán hàng',
                'name'        => 'Báo cáo chi tiết bán hàng theo xe',
                'description' => 'Tạo báo cáo chi tiết bán hàng theo xe',
                'action'      => 'report',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý bán hàng',
                'name'        => 'Báo cáo tồn quỹ',
                'description' => 'Tạo báo cáo tồn quỹ',
                'action'      => 'report',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý bán hàng',
                'name'        => 'Báo cáo thu chi',
                'description' => 'Tạo báo cáo thu chi',
                'action'      => 'report',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý bán hàng',
                'name'        => 'Báo cáo lệnh chuyển đổi tiền tệ',
                'description' => 'Tạo báo cáo lệnh chuyển đổi tiền tệ',
                'action'      => 'report',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý bán hàng',
                'name'        => 'Báo cáo lãi theo kho/bãi',
                'description' => 'Tạo báo cáo lãi theo kho/bãi',
                'action'      => 'report',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý bán hàng',
                'name'        => 'Báo cáo tổng hợp thu mua',
                'description' => 'Tạo báo cáo tổng hợp thu mua',
                'action'      => 'report',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý bán hàng',
                'name'        => 'Danh sách phiếu cân tự động',
                'description' => 'Hiển thị danh sách phiếu cân tự động',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            // Phụ trách chi phí hải quan
            [
                'group'       => 'Phụ trách chi phí hải quan',
                'name'        => 'Lập phiếu theo dõi',
                'description' => 'Tạo phiếu theo dõi',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Phụ trách chi phí hải quan',
                'name'        => 'Lập phiếu thu tiền',
                'description' => 'Tạo phiếu thu tiền',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Phụ trách chi phí hải quan',
                'name'        => 'Thanh toán công nợ: Lựa chọn danh sách phiếu theo dõi sẽ thanh toán',
                'description' => 'Chọn danh sách phiếu theo dõi để thanh toán công nợ',
                'action'      => 'select',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Phụ trách chi phí hải quan',
                'name'        => 'Thanh toán công nợ: Lập phiếu thanh toán công nợ theo phiếu theo dõi',
                'description' => 'Tạo phiếu thanh toán công nợ theo phiếu theo dõi',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Phụ trách chi phí hải quan',
                'name'        => 'Thanh toán công nợ: Lập phiếu thanh toán công nợ không theo phiếu theo dõi',
                'description' => 'Tạo phiếu thanh toán công nợ không theo phiếu theo dõi',
                'action'      => 'create',
                'method'      => 'POST'
            ],
            [
                'group'       => 'Phụ trách chi phí hải quan',
                'name'        => 'Danh sách phiếu theo dõi',
                'description' => 'Hiển thị danh sách phiếu theo dõi',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Phụ trách chi phí hải quan',
                'name'        => 'Danh sách phiếu thu/chi',
                'description' => 'Hiển thị danh sách phiếu thu/chi',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Phụ trách chi phí hải quan',
                'name'        => 'Quản lý danh sách mục đích',
                'description' => 'Quản lý danh sách mục đích',
                'action'      => 'manage',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Phụ trách chi phí hải quan',
                'name'        => 'Báo cáo tổng quan tình hình quỹ',
                'description' => 'Tạo báo cáo tổng quan tình hình quỹ',
                'action'      => 'report',
                'method'      => 'GET'
            ],
            // Quản lý chi phí hải quan
            [
                'group'       => 'Quản lý chi phí hải quan',
                'name'        => 'Báo cáo tổng quan tình hình quỹ',
                'description' => 'Tạo báo cáo tổng quan tình hình quỹ',
                'action'      => 'report',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý chi phí hải quan',
                'name'        => 'Danh sách phiếu theo dõi',
                'description' => 'Hiển thị danh sách phiếu theo dõi',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý chi phí hải quan',
                'name'        => 'Danh sách phiếu thu/chi',
                'description' => 'Hiển thị danh sách phiếu thu/chi',
                'action'      => 'view',
                'method'      => 'GET'
            ],
            // Quản lý hệ thống
            [
                'group'       => 'Quản lý hệ thống',
                'name'        => 'Quản lý danh sách bãi',
                'description' => 'Quản lý danh sách bãi',
                'action'      => 'manage',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý hệ thống',
                'name'        => 'Quản lý danh sách kho tại Việt Nam',
                'description' => 'Quản lý danh sách kho tại Việt Nam',
                'action'      => 'manage',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý hệ thống',
                'name'        => 'Quản lý đơn vị quỹ tiền mặt',
                'description' => 'Quản lý đơn vị quỹ tiền mặt',
                'action'      => 'manage',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý hệ thống',
                'name'        => 'Quản lý chủng loại mặt hàng',
                'description' => 'Quản lý chủng loại mặt hàng',
                'action'      => 'manage',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý hệ thống',
                'name'        => 'Quản lý nhà máy',
                'description' => 'Quản lý nhà máy',
                'action'      => 'manage',
                'method'      => 'GET'
            ],
            [
                'group'       => 'Quản lý hệ thống',
                'name'        => 'Quản lý người dùng',
                'description' => 'Quản lý người dùng',
                'action'      => 'manage',
                'method'      => 'GET'
            ],
        ];
        foreach ($permissionsData as $perm) {
            $db->table('permissions')->insert($perm);
        }

        // c. Gán tự động permissions cho role tương ứng dựa trên cột group
        $roleTable  = $db->table('roles');
        $permTable  = $db->table('permissions');
        $pivotTable = $db->table('role_permission');

        foreach ($rolesData as $role) {
            // Lấy role theo tên
            $roleRow = $roleTable->where('name', $role['name'])->get()->getRow();
            if (!$roleRow) {
                continue;
            }
            // Lấy các permission có cột group trùng với tên role
            $permRows = $permTable->where('group', $role['name'])->get()->getResult();
            if ($permRows) {
                foreach ($permRows as $perm) {
                    $pivotTable->insert([
                        'role_id'       => $roleRow->id,
                        'permission_id' => $perm->id,
                    ]);
                }
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('role_permission');
        $this->forge->dropTable('permissions');
        $this->forge->dropTable('roles');
    }
}
