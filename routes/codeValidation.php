<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

$merchantID = env('EZPAY_INVOICE_MERCHANT_ID');
$merchantHashKey = env('EZPAY_INVOICE_MERCHANT_HASH_KEY');
$merchantHashIV = env('EZPAY_INVOICE_MERCHANT_HASH_IV');
$companyID = env('EZPAY_INVOICE_COMPANY_ID');
$companyHashKey = env('EZPAY_INVOICE_COMPANY_HASH_KEY');
$companyHashIV = env('EZPAY_INVOICE_COMPANY_HASH_IV');
$baseUrl = 'https://cinv.ezpay.com.tw';

// 4. 手機條碼與捐贈碼驗證

Route::prefix('/alphanumericCode')->group(function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {

    // 4-1. 手機條碼驗證

    // 4-2. 捐贈碼驗證

});
