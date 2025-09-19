<?php

use Agriweather\EzPayInvoice\Enums\Invoice\TaxType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Illuminate\Support\Facades\Route;

// 1. 電子發票

Route::prefix('/invoice')->group(function () {

    // Class API 方法名稱
    // toArray() => 呼叫 toFormData() 方法
    // toFormData()
    // submit()
    // redirectToEZPay()

    // 1-1. 開立發票
    // 1-1-1. 即時開立發票
    Route::get('/instant', function () {
        $result = EzPayInvoice::invoice()
            ->create()
            ->withOrder('Order'.time())
            ->forConsumer('John Doe')
            ->withItem('測試商品', quantity: 1, unit: '個', price: 1000, amount: 1000)
            ->withTax(TaxType::TAXABLE, 5)
            ->withAmount(1000, 50, 1050)
            ->issue();

        return $result;
    });

    // 1-1-2. 等待觸發開立發票
    Route::get('/pending', function () {
        $result = EzPayInvoice::invoice()
            ->create()
            ->withOrder('Order'.time())
            ->forConsumer('John Doe')
            ->withItem('測試商品', quantity: 1, unit: '個', price: 1000, amount: 1000)
            ->withTax(TaxType::TAXABLE, 5)
            ->withAmount(1000, 50, 1050)
            ->deferIssue();

        return $result;
    });

    // 觸發開立發票
    Route::get('/trigger/{invoiceTransNo}/{merchantOrderNo}', function (string $invoiceTransNo, string $merchantOrderNo) {
        $result = EzPayInvoice::invoice()
            ->pending()
            ->withInvoiceTransNo($invoiceTransNo)
            ->withOrder($merchantOrderNo)
            ->withTotalAmount(1000)
            ->trigger();

        return $result;
    });

    // 1-1-3. 預約自動開立發票
    Route::get('/scheduled', function () {
        //
    });

    // 馬上觸發開立發票

    // 1-2. 作廢發票
    Route::get('/void/{invoiceNumber}', function (string $invoiceNumber) {
        $result = EzPayInvoice::invoice()
            ->voidable()
            ->withInvoice($invoiceNumber)
            ->because('客戶取消訂單')
            ->invalidate();

        return $result;
    });

    // 1-3. 開立折讓
    // 1-3-1. 不立即確認折讓
    Route::get('/allowances/pending/{invoiceNumber}/{merchantOrderNo}', function (string $invoiceNumber, string $merchantOrderNo) {
        $result = EzPayInvoice::allowance()
            ->create()
            ->withInvoice($invoiceNumber)
            ->withOrder($merchantOrderNo)
            ->withItem('測試商品', quantity: 1, unit: '個', price: 800, amount: 800, taxAmount: 0)
            ->withTotalAmount(800)
            ->issuePendingConfirmation();

        return $result;
    });

    // 觸發確認折讓
    Route::get('/allowances/confirm/{allowanceNo}/{merchantOrderNo}', function (string $allowanceNo, string $merchantOrderNo) {
        $result = EzPayInvoice::allowance()
            ->pending()
            ->withAllowance($allowanceNo)
            ->withOrder($merchantOrderNo)
            ->withTotalAmount(800)
            ->confirm();

        return $result;
    });

    // 觸發取消折讓
    Route::get('/allowances/cancel/{allowanceNo}/{merchantOrderNo}', function (string $allowanceNo, string $merchantOrderNo) {
        $result = EzPayInvoice::allowance()
            ->pending()
            ->withAllowance($allowanceNo)
            ->withOrder($merchantOrderNo)
            ->withTotalAmount(800)
            ->cancel();

        return $result;
    });

    // 1-3-2. 立即確認折讓
    Route::get('/allowances/instant/{invoiceNumber}/{merchantOrderNo}', function (string $invoiceNumber, string $merchantOrderNo) {
        $result = EzPayInvoice::allowance()
            ->create()
            ->withInvoice($invoiceNumber)
            ->withOrder($merchantOrderNo)
            ->withItem('測試商品', quantity: 1, unit: '個', price: 800, amount: 800, taxAmount: 0)
            ->withTotalAmount(800)
            ->issue();

        return $result;
    });

    // 1-4. 作廢折讓－作廢已確認折讓
    Route::get('/allowances/void/{allowanceNo}', function (string $allowanceNo) {
        $result = EzPayInvoice::allowance()
            ->voidable()
            ->withAllowance($allowanceNo)
            ->because('作廢原因')
            ->invalidate();

        return $result;
    });

    // 1-5. 發票查詢
    Route::get('/search/invoice/{invoiceNumber}/{randomNum}', function (string $invoiceNumber, string $randomNum) {
        $result = EzPayInvoice::invoice()
            ->query()
            ->withInvoice($invoiceNumber)
            ->withRandomNumber($randomNum)
            ->get();

        return $result;
    });
    Route::get('/search/order/{merchantOrderNo}/{totalAmt}', function (string $merchantOrderNo, string $totalAmt) {
        $result = EzPayInvoice::invoice()
            ->query()
            ->withOrder($merchantOrderNo)
            ->withTotalAmount((int) $totalAmt)
            ->get();

        return $result;
    });
    Route::get('/search/html/{invoiceNumber}/{randomNum}', function (string $invoiceNumber, string $randomNum) {
        return EzPayInvoice::invoice()
            ->query()
            ->withInvoice($invoiceNumber)
            ->withRandomNumber($randomNum)
            ->redirectToEzPay();
    });
    Route::get('/search/url/{merchantOrderNo}/{totalAmt}', function (string $merchantOrderNo, string $totalAmt) {
        return EzPayInvoice::invoice()
            ->query()
            ->withOrder($merchantOrderNo)
            ->withTotalAmount((int) $totalAmt)
            ->getEzPaySearchUrl();
    });

});
