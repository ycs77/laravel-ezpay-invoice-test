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

Route::prefix('/codeValidation')->group(function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {

    // 4-1. 手機條碼驗證
    Route::get('/barcode', function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'TimeStamp' => time(),
            'CellphoneBarcode' => '/AAA.CCC',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'Version' => '1.0',
            'RespondType' => 'JSON',
            'PostData_' => $encryptedPostData,
            'CheckValue' => checkValue($encryptedPostData, $merchantHashKey, $merchantHashIV),
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api_inv_application/checkBarCode', $transactionData);

        $result = $response->json();

        parse_str(removepadding(openssl_decrypt(hex2bin(trim($result['Result'])), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)), $result['Result']);

        return $result;
    });

    // 4-2. 捐贈碼驗證

});
