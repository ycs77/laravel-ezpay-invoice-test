<?php

use Agriweather\EzPayInvoice\Facades\EzPayInvoice;
use Illuminate\Support\Facades\Route;

// 4. 手機條碼與捐贈碼驗證

Route::prefix('/codeValidation')->group(function () {

    // 4-1. 手機條碼驗證
    Route::get('/barcode', function () {
        $result = EzPayInvoice::codeValidation()
            ->withBarcode('/AAA.CCC')
            ->check();

        return $result;
    });

    // 4-2. 捐贈碼驗證
    Route::get('/lovecode', function () {
        $result = EzPayInvoice::codeValidation()
            ->withLoveCode('123')
            ->check();

        return $result;
    });

});
