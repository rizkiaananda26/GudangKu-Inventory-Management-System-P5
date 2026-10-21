<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - GudangKu Inventory Management System
|--------------------------------------------------------------------------
*/

// Rute Tamu (Autentikasi Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Rute Logout (Perlu Login)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rute Terproteksi Login (Semua Petugas & Admin)
Route::middleware('auth')->group(function () {
    // 1. Dashboard Utama
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // 2. Resource Controller Produk
    Route::resource('products', ProductController::class);

    // 3. Resource Controller Kategori & Supplier
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('suppliers', SupplierController::class)->except(['create', 'show', 'edit']);

    // 4. Transaksi Mutasi Stok
    Route::get('/stock', [StockTransactionController::class, 'index'])->name('stock.index');
    Route::post('/stock', [StockTransactionController::class, 'store'])->name('stock.store');

    // 5. Laporan Persediaan & Mutasi
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // 6. Rute Khusus Administrator (Kelola Pengguna)
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
    });
});



