<?php

use Illuminate\Support\Facades\Route;

include_once 'helper.php';
include_once 'invoice.php';
include_once 'crossBorder.php';
include_once 'alphanumericCode.php';
include_once 'codeValidation.php';

Route::get('/', function () {
    return view('welcome');
});
