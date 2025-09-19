<?php

use Agriweather\EzPayInvoice\Enums\Invoice\CurrencyType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Illuminate\Support\Facades\Route;

// 1. 電子發票

Route::prefix('/crossBorder')->group(function () {

    // 1-1. 開立發票
    // 1-1-1. 即時開立發票
    Route::get('/instant', function () {
        $result = EzPayInvoice::crossBorder()
            ->invoice()
            ->create()
            ->withOrder('CBOrder'.time()) // 訂單編號
            ->withCustomer('John Doe') // 買受人姓名
            ->withEmail('customer@example.com') // 買受人電子信箱
            ->withCurrency(CurrencyType::USD) // 幣別：USD 美元
            ->withItem('國際商品', quantity: 1, unit: 'EA', price: 105.5, amount: 105.5) // 商品名稱、數量、單位、單價和金額
            ->withAmount(100.0, 5.5, 105.5) // 未稅銷售額、稅額、含稅銷售額
            ->withOriginalCurrencyAmount(100.0) // 營業人備註之原幣金額
            ->withExchangeRate(30.5) // 營業人備註之匯率
            ->issue();

        return $result;
    });

    // 1-3. 開立折讓
    // 1-3-1. 不立即確認折讓
    Route::get('/allowances/pending/{invoiceNumber}/{merchantOrderNo}', function (string $invoiceNumber, string $merchantOrderNo) {
        $result = EzPayInvoice::crossBorder()
            ->allowance()
            ->create()
            ->withInvoice($invoiceNumber)
            ->withOrder($merchantOrderNo)
            ->withItem('國際商品', quantity: 1, unit: '個', price: 105.5, amount: 105.5)
            ->withTotalAmount(105.5)
            ->issuePendingConfirmation();

        return $result;
    });

    // 觸發確認折讓
    Route::get('/allowances/confirm/{allowanceNo}/{merchantOrderNo}', function (string $allowanceNo, string $merchantOrderNo) {
        $result = EzPayInvoice::crossBorder()
            ->allowance()
            ->pending()
            ->withAllowance($allowanceNo)
            ->withOrder($merchantOrderNo)
            ->withTotalAmount(105.5)
            ->confirm();

        return $result;
    });

    // 觸發取消折讓
    Route::get('/allowances/cancel/{allowanceNo}/{merchantOrderNo}', function (string $allowanceNo, string $merchantOrderNo) {
        $result = EzPayInvoice::crossBorder()
            ->allowance()
            ->pending()
            ->withAllowance($allowanceNo)
            ->withOrder($merchantOrderNo)
            ->withTotalAmount(105.5)
            ->cancel();

        return $result;
    });

    // 1-3-2. 立即確認折讓
    Route::get('/allowances/instant/{invoiceNumber}/{merchantOrderNo}', function (string $invoiceNumber, string $merchantOrderNo) {
        $result = EzPayInvoice::crossBorder()
            ->allowance()
            ->create()
            ->withInvoice($invoiceNumber)
            ->withOrder($merchantOrderNo)
            ->withItem('國際商品', quantity: 1, unit: '個', price: 105.5, amount: 105.5)
            ->withTotalAmount(105.5)
            ->issue();

        return $result;
    });

    // 1-5. 發票查詢
    Route::get('/search/invoice/{invoiceNumber}/{randomNum}', function (string $invoiceNumber, string $randomNum) {
        $result = EzPayInvoice::crossBorder()
            ->invoice()
            ->query()
            ->withInvoice($invoiceNumber)
            ->withRandomNumber($randomNum)
            ->get();

        return $result;
    });
    Route::get('/search/order/{merchantOrderNo}/{totalAmt}', function (string $merchantOrderNo, string $totalAmt) {
        $result = EzPayInvoice::crossBorder()
            ->invoice()
            ->query()
            ->withOrder($merchantOrderNo)
            ->withTotalAmount((int) $totalAmt)
            ->get();

        return $result;
    });
    Route::get('/search/html/{invoiceNumber}/{randomNum}', function (string $invoiceNumber, string $randomNum) {
        return EzPayInvoice::crossBorder()
            ->invoice()
            ->query()
            ->withInvoice($invoiceNumber)
            ->withRandomNumber($randomNum)
            ->redirectToEzPay();
    });

});
