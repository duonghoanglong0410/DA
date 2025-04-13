<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SwapCurrencyFundColumns extends Migration
{
    public function up()
    {
        // cung cấp migration đồng thời sửa lại file erDiagram
        // - đổi tên cột trip_details.buyer_currency_fund_id thành trip_details.purchase_yard_currency_fund_id 
        // - đổi tên cột trips.purchase_yard_currency_fund_id thành trips.buyer_currency_fund_id  
        
        // Copy data to temporary columns
        $this->db->query("ALTER TABLE `trip_details` CHANGE `buyer_currency_fund_id` `purchase_yard_currency_fund_id` INT UNSIGNED NOT NULL COMMENT 'Mã quỹ tiền tệ theo bãi'; ");
        $this->db->query("ALTER TABLE `trips` CHANGE `purchase_yard_currency_fund_id` `buyer_currency_fund_id` INT UNSIGNED NOT NULL COMMENT 'Mã quỹ tiền tệ của nhà máy mua hàng'; ");
    }

    public function down()
    {
       $this->db->query("ALTER TABLE `trip_details` CHANGE `purchase_yard_currency_fund_id` `buyer_currency_fund_id` INT UNSIGNED NOT NULL COMMENT 'Mã quỹ tiền tệ theo bãi'; ");
       $this->db->query("ALTER TABLE `trips` CHANGE `buyer_currency_fund_id` `purchase_yard_currency_fund_id` INT UNSIGNED NOT NULL COMMENT 'Mã quỹ tiền tệ của nhà máy mua hàng'; ");
    }
}
