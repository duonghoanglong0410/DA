<?php

namespace App\Constants;

class Roles
{
    // Giá trị role (số, tương ứng với id trong bảng roles)
    public const YARD_EMPLOYEE          = 1;  // Nhân viên bãi
    public const YARD_CASHIER           = 2;  // Thủ quỹ của bãi
    public const DISPATCHER             = 3;  // Điều phối xe
    public const FINANCE_CONTROLLER     = 4;  // Kiểm soát tài chính kho bãi
    public const WAREHOUSE_MANAGER      = 5;  // Quản lý kho
    public const DEBT_ACCOUNTANT        = 6;  // Kế toán công nợ
    public const CASHIER                = 7;  // Thủ quỹ
    public const SALES_MANAGER          = 8;  // Quản lý bán hàng
    public const CUSTOMS_COST_CONTROLLER= 9;  // Phụ trách chi phí hải quan
    public const CUSTOMS_COST_MANAGER   = 10; // Quản lý chi phí hải quan
    public const SYSTEM_MANAGER         = 11; // Quản lý hệ thống

    // Các chuỗi menu key dùng trong giao diện (định dạng tiếng Anh, in hoa, dấu cách thay bằng dấu gạch dưới)
    public const MENU_PURCHASE_YARD_EMPLOYEE   = 'PURCHASE_YARD_EMPLOYEE';
    public const MENU_PURCHASE_YARD_CASHIER    = 'PURCHASE_YARD_CASHIER';
    public const MENU_DISPATCHER               = 'DISPATCHER';
    public const MENU_FINANCE_CONTROLLER       = 'FINANCE_CONTROLLER';
    public const MENU_WAREHOUSE_MANAGER        = 'WAREHOUSE_MANAGER';
    public const MENU_DEBT_ACCOUNTANT          = 'DEBT_ACCOUNTANT';
    public const MENU_CASHIER                  = 'CASHIER';
    public const MENU_SALES_MANAGER            = 'SALES_MANAGER';
    public const MENU_CUSTOMS_COST_CONTROLLER  = 'CUSTOMS_COST_CONTROLLER';
    public const MENU_CUSTOMS_COST_MANAGER     = 'CUSTOMS_COST_MANAGER';
    public const MENU_SYSTEM_MANAGER           = 'SYSTEM_MANAGER';

    public const ROLES_MAPPING = [
        Roles::YARD_EMPLOYEE           => Roles::MENU_PURCHASE_YARD_EMPLOYEE,
        Roles::YARD_CASHIER            => Roles::MENU_PURCHASE_YARD_CASHIER,
        Roles::DISPATCHER              => Roles::MENU_DISPATCHER,
        Roles::FINANCE_CONTROLLER      => Roles::MENU_FINANCE_CONTROLLER,
        Roles::WAREHOUSE_MANAGER       => Roles::MENU_WAREHOUSE_MANAGER,
        Roles::DEBT_ACCOUNTANT         => Roles::MENU_DEBT_ACCOUNTANT,
        Roles::CASHIER                 => Roles::MENU_CASHIER,
        Roles::SALES_MANAGER           => Roles::MENU_SALES_MANAGER,
        Roles::CUSTOMS_COST_CONTROLLER => Roles::MENU_CUSTOMS_COST_CONTROLLER,
        Roles::CUSTOMS_COST_MANAGER    => Roles::MENU_CUSTOMS_COST_MANAGER,
        Roles::SYSTEM_MANAGER          => Roles::MENU_SYSTEM_MANAGER,
    ];    
}
