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

// 1. 電子發票

Route::prefix('/crossBorder')->group(function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {

    // 1-1. 開立發票
    // 1-1-1. 即時開立發票
    Route::get('/instant', function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            'MerchantOrderNo' => 'CBOrder'.time(),
            'Status' => '1',
            'BuyerName' => 'John Doe',
            'BuyerEmail' => 'customer@example.com',
            'Amt' => '100.00',
            'TaxAmt' => '5.50',
            'TotalAmt' => '105.50',
            'ItemName' => '國際商品',
            'ItemCount' => '1',
            'ItemUnit' => 'EA',
            'ItemPrice' => '105.50',
            'ItemAmt' => '105.50',
            'Currency' => 'USD',
            'OriginalCurrencyAmount' => '100.00',
            'ExchangeRate' => '30.5',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/crossBorderInvoiceIssue', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });

    // 1-3. 開立折讓
    // 1-3-1. 不立即確認折讓
    Route::get('/allowances/pending/{invoiceNumber}/{merchantOrderNo}', function (string $invoiceNumber, string $merchantOrderNo) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            'InvoiceNo' => $invoiceNumber,
            'MerchantOrderNo' => $merchantOrderNo,
            'ItemName' => '國際商品',
            'ItemCount' => '1',
            'ItemUnit' => 'EA',
            'ItemPrice' => '105.50',
            'ItemAmt' => '105.50',
            'ItemTaxAmt' => '0',
            'TotalAmt' => '105.50',
            'Status' => '0',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/crossBorderAllowanceIssue', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });

    // 觸發確認折讓
    Route::get('/allowances/confirm/{allowanceNo}/{merchantOrderNo}', function (string $allowanceNo, string $merchantOrderNo) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            'AllowanceStatus' => 'C',
            'AllowanceNo' => $allowanceNo,
            'MerchantOrderNo' => $merchantOrderNo,
            'TotalAmt' => '800',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/allowance_touch_issue', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });

    // 1-3-2. 立即確認折讓
    Route::get('/allowances/instant/{invoiceNumber}/{merchantOrderNo}', function (string $invoiceNumber, string $merchantOrderNo) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.3',
            'TimeStamp' => time(),
            'InvoiceNo' => $invoiceNumber,
            'MerchantOrderNo' => $merchantOrderNo,
            'ItemName' => '測試商品',
            'ItemCount' => '1',
            'ItemUnit' => '個',
            'ItemPrice' => '800',
            'ItemAmt' => '800',
            'ItemTaxAmt' => '0',
            'TotalAmt' => '800',
            'Status' => '1',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/allowance_issue', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });

    // 1-5. 發票查詢
    Route::get('/search/invoice/{invoiceNumber}/{randomNum}', function (string $invoiceNumber, string $randomNum) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            'SearchType' => '0',
            'MerchantOrderNo' => '',
            'TotalAmt' => '',
            'InvoiceNumber' => $invoiceNumber,
            'RandomNum' => $randomNum,
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/crossBorderInvoiceSearch', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });
    Route::get('/search/order/{merchantOrderNo}/{totalAmt}', function (string $merchantOrderNo, string $totalAmt) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            'SearchType' => '1',
            'MerchantOrderNo' => $merchantOrderNo,
            'TotalAmt' => $totalAmt,
            'InvoiceNumber' => '',
            'RandomNum' => '',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/crossBorderInvoiceSearch', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });
    Route::get('/search/html/{invoiceNumber}/{randomNum}', function (string $invoiceNumber, string $randomNum) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            'SearchType' => '0',
            'MerchantOrderNo' => '',
            'TotalAmt' => '',
            'InvoiceNumber' => $invoiceNumber,
            'RandomNum' => $randomNum,
            'DisplayFlag' => '1',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));

        return response(<<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
    </head>

    <body>
        <form id="order-form" action="{$baseUrl}/Api/crossBorderInvoiceSearch" method="post">
            <input type="hidden" name="MerchantID_" value="{$merchantID}">
            <input type="hidden" name="PostData_" value="{$encryptedPostData}">
            <input type="submit">
        </form>

        <script>document.getElementById("order-form").submit();</script>
    </body>
</html>
HTML)->header('Content-Type', 'text/html');
    });

});
