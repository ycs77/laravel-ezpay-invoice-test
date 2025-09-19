<?php

use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceTerm;
use Agriweather\EzPayInvoice\Enums\Invoice\InvoiceType;
use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Illuminate\Support\Facades\Route;

// 3. 電子發票 字軌管理

Route::prefix('/alphanumericCode')->group(function () {

    // 3-1. 新增字軌
    Route::get('/create', function () {
        $result = EzPayInvoice::alphanumericCode()
            ->create()
            ->withYear(114) // 民國年，只可輸入今年與明年。
            ->withTerm(InvoiceTerm::JUL_AUG) // 發票期別：07-08月
            ->withCode('AA') // 字軌英文代碼
            ->withRange('00000100', '00000199') // 發票號碼範圍
            ->withType(InvoiceType::GENERAL) // 發票類別：`InvoiceType::GENERAL` (07: 一般稅額計算)
            ->save();

        return $result;
    });

    // 3-2. 字軌資料管理
    Route::get('/modify/{managementNo}', function (string $managementNo) {
        $result = EzPayInvoice::alphanumericCode()
            ->query()
            ->withNo('0t0ghr0fyv')
            ->withYear(114)
            ->pause();

        return $result;
    });

    // 3-3. 字軌資料查詢
    Route::get('/search', function () {
        $alphanumericCodeResults = EzPayInvoice::alphanumericCode()
            ->query()
            ->withYear(114)
            ->withTerm(InvoiceTerm::JUL_AUG)
            ->get();

        return response()->json($alphanumericCodeResults->toArray());
    });

});
