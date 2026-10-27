<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

// Halaman Utama
Route::get('/', function () {
    require base_path('index.php');
    return '';
});

Route::get('/ulinkaciamis', function () {
    require base_path('index.php');
    return '';
});

Route::get('/ulinkaciamis/', function () {
    require base_path('index.php');
    return '';
});

// API Destinasi
Route::any('/api/destinasi.php', function () {
    require base_path('api/destinasi.php');
    return '';
});

Route::any('/ulinkaciamis/api/destinasi.php', function () {
    require base_path('api/destinasi.php');
    return '';
});

// Admin Panel Routes
Route::any('/admin', function () {
    require base_path('admin/dashboard.php');
    return '';
});

Route::any('/admin/{file}', function ($file) {
    $target = base_path('admin/' . $file);
    if (file_exists($target)) {
        require $target;
        return '';
    }
    abort(404);
})->where('file', '.*');

Route::any('/ulinkaciamis/admin/{file}', function ($file) {
    $target = base_path('admin/' . $file);
    if (file_exists($target)) {
        require $target;
        return '';
    }
    abort(404);
})->where('file', '.*');

Route::any('/login', function () {
    require base_path('admin/login.php');
    return '';
});

Route::any('/ulinkaciamis/login', function () {
    require base_path('admin/login.php');
    return '';
});

// Static Assets Handler (Support both /assets/... and /ulinkaciamis/assets/...)
function serveProjectAsset($relativePath) {
    $filePath = base_path('assets/' . $relativePath);
    if (!file_exists($filePath) || is_dir($filePath)) {
        abort(404);
    }
    $mime = mime_content_type($filePath);
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($ext === 'webp') {
        $mime = 'image/webp';
    } elseif ($ext === 'css') {
        $mime = 'text/css';
    } elseif ($ext === 'js') {
        $mime = 'application/javascript';
    }
    return response()->file($filePath, ['Content-Type' => $mime]);
}

Route::get('/assets/{path}', function ($path) {
    return serveProjectAsset($path);
})->where('path', '.*');

Route::get('/ulinkaciamis/assets/{path}', function ($path) {
    return serveProjectAsset($path);
})->where('path', '.*');

// Public Images Handler
Route::get('/ulinkaciamis/public/images/{path}', function ($path) {
    $filePath = public_path('images/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    return response()->file($filePath);
})->where('path', '.*');

Route::get('/public/images/{path}', function ($path) {
    $filePath = public_path('images/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    return response()->file($filePath);
})->where('path', '.*');
