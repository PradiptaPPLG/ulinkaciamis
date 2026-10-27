<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    require base_path('index.php');
    return '';
});

Route::any('/login', function () {
    require base_path('admin/login.php');
    return '';
});
