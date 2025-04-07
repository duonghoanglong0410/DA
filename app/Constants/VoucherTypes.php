<?php namespace App\Constants;

class VoucherTypes
{
    // Expense Vouchers
    const EXTERNAL_EXPENSE = 101;    // Phiếu chi ngoài
    const INTERNAL_EXPENSE = 102;    // Phiếu chi nội bộ
    const FREIGHT_EXPENSE  = 103;    // Phiếu chi tiền cước
    const CUSTOMS_EXPENSE  = 104;    // Phiếu chi hải quan
    const OTHER_EXPENSE    = 105;    // Phiếu chi chi phí khác

    // Collection Vouchers
    const CUSTOMER_COLLECTION = 201; // Thu công nợ khách hàng
    const OTHER_COLLECTION    = 202; // Thu khác

    // Currency Exchange Voucher
    const CURRENCY_EXCHANGE   = 301; // Phiếu đổi tiền
}
