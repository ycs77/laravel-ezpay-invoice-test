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

// 3. 電子發票 字軌管理

Route::prefix('/alphanumericCode')->group(function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {

    // 3-1. 新增字軌
    Route::get('/create', function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            'Year' => '114',
            'Term' => '4',
            'AphabeticLetter' => 'AA',
            'StartNumber' => '00000100',
            'EndNumber' => '00000199',
            'Type' => '07',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $companyHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $companyHashIV)));
        $transactionData = [
            'CompanyID_' => $companyID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api_number_management/createNumber', $transactionData);

        checkAlphanumericCode($response->json(), $companyHashKey, $companyHashIV);

        return response()->json($response->json());
    });

    // 3-2. 字軌資料管理
    Route::get('/modify/{managementNo}', function (string $managementNo) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            'ManagementNo' => $managementNo,
            'Year' => '114',
            'Flag' => '0',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $companyHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $companyHashIV)));
        $transactionData = [
            'CompanyID_' => $companyID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api_number_management/manageNumber', $transactionData);

        checkAlphanumericCode($response->json(), $companyHashKey, $companyHashIV);

        return response()->json($response->json());
    });

    // 3-3. 字軌資料查詢
    Route::get('/search', function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            // 'ManagementNo' => '1234567890',
            'Year' => '114',
            'Term' => '4',
            // 'Flag' => '0',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $companyHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $companyHashIV)));
        $transactionData = [
            'CompanyID_' => $companyID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api_number_management/searchNumber', $transactionData);

        checkAlphanumericCode($response->json(), $companyHashKey, $companyHashIV);

        return response()->json($response->json());
    });

});
