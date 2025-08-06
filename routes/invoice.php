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

Route::prefix('/invoice')->group(function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {

    // Class API 方法名稱
    // toArray() => 呼叫 toFormData() 方法
    // toFormData()
    // submit()
    // redirectToEZPay()

    // 1-1. 開立發票
    // 1-1-1. 即時開立發票
    Route::get('/instant', function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => time(),
            'MerchantOrderNo' => 'Order'.time(),
            'Status' => '1',
            'Category' => 'B2C',
            'BuyerName' => 'John Doe',
            'CarrierType' => '',
            'LoveCode' => '',
            'PrintFlag' => 'Y',
            'TaxType' => '1',
            'TaxRate' => '5',
            'Amt' => '1000',
            'TaxAmt' => '50',
            'TotalAmt' => '1050',
            'ItemName' => '測試商品',
            'ItemCount' => '1',
            'ItemUnit' => '個',
            'ItemPrice' => '1000',
            'ItemAmt' => '1000',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/invoice_issue', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });

    // 1-1-2. 等待觸發開立發票
    Route::get('/pending', function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => time(),
            'MerchantOrderNo' => 'Order'.time(),
            'Status' => '0',
            'Category' => 'B2C',
            'BuyerName' => 'John Doe',
            'CarrierType' => '',
            'LoveCode' => '',
            'PrintFlag' => 'Y',
            'TaxType' => '3',
            'TaxRate' => '0',
            'Amt' => '1000',
            'TaxAmt' => '0',
            'TotalAmt' => '1000',
            'ItemName' => '測試商品',
            'ItemCount' => '1',
            'ItemUnit' => '個',
            'ItemPrice' => '1000',
            'ItemAmt' => '1000',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/invoice_issue', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });

    // 觸發開立發票
    Route::get('/trigger/{invoiceTransNo}/{merchantOrderNo}', function (string $invoiceTransNo, string $merchantOrderNo) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            'InvoiceTransNo' => $invoiceTransNo,
            'MerchantOrderNo' => $merchantOrderNo,
            'TotalAmt' => '1000',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/invoice_touch_issue', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });

    // 1-1-3. 預約自動開立發票
    Route::get('/scheduled', function () use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        //
    });

    // 馬上觸發開立發票

    // 1-2. 作廢發票
    Route::get('/void/{invoiceNumber}', function (string $invoiceNumber) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            'InvoiceNumber' => $invoiceNumber,
            'InvalidReason' => '作廢原因',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/invoice_invalid', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });

    // 1-3. 開立折讓
    // 1-3-1. 不立即確認折讓
    Route::get('/allowances/pending/{invoiceNumber}/{merchantOrderNo}', function (string $invoiceNumber, string $merchantOrderNo) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
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
            ->post($baseUrl.'/Api/allowance_issue', $transactionData);

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

    // 觸發取消折讓

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

    // 1-4. 作廢折讓－作廢已確認折讓
    Route::get('/allowances/void/{allowanceNo}', function (string $allowanceNo) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => time(),
            'AllowanceNo' => $allowanceNo,
            'InvalidReason' => '作廢原因',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/allowanceInvalid', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });

    // 1-5. 發票查詢
    Route::get('/search/invoice/{invoiceNumber}/{randomNum}', function (string $invoiceNumber, string $randomNum) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.3',
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
            ->post($baseUrl.'/Api/invoice_search', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });
    Route::get('/search/order/{merchantOrderNo}/{totalAmt}', function (string $merchantOrderNo, string $totalAmt) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.3',
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
            ->post($baseUrl.'/Api/invoice_search', $transactionData);

        checkCode($response->json(), $merchantHashKey, $merchantHashIV);

        return response()->json($response->json());
    });
    Route::get('/search/html/{invoiceNumber}/{randomNum}', function (string $invoiceNumber, string $randomNum) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.3',
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
        <form id="order-form" action="{$baseUrl}/Api/invoice_search" method="post">
            <input type="hidden" name="MerchantID_" value="{$merchantID}">
            <input type="hidden" name="PostData_" value="{$encryptedPostData}">
            <input type="submit">
        </form>

        <script>document.getElementById("order-form").submit();</script>
    </body>
</html>
HTML)->header('Content-Type', 'text/html');
    });
    Route::get('/search/url/{merchantOrderNo}/{totalAmt}', function (string $merchantOrderNo, string $totalAmt) use ($merchantID, $merchantHashKey, $merchantHashIV, $companyID, $companyHashKey, $companyHashIV, $baseUrl) {
        $postData = [
            'RespondType' => 'JSON',
            'Version' => '1.3',
            'TimeStamp' => time(),
            'SearchType' => '1',
            'MerchantOrderNo' => $merchantOrderNo,
            'TotalAmt' => $totalAmt,
            'InvoiceNumber' => '',
            'RandomNum' => '',
            'DisplayFlag' => '2',
        ];

        $postDataStr = http_build_query($postData);
        $encryptedPostData = trim(bin2hex(openssl_encrypt(addpadding($postDataStr), 'AES-256-CBC', $merchantHashKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $merchantHashIV)));
        $transactionData = [
            'MerchantID_' => $merchantID,
            'PostData_' => $encryptedPostData,
        ];

        $response = Http::asForm()
            ->withUserAgent('ezPay')
            ->post($baseUrl.'/Api/invoice_search', $transactionData);

        return response()->json($response->json());
    });

});
