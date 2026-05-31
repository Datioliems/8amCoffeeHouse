<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\BanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockCheckController;
use App\Http\Controllers\NguyenLieuController;
use App\Http\Controllers\ReportController;

Route::get('/', fn() => redirect()->route('login'));

// ── CUSTOMER ──────────────────────────────────────────────────
Route::prefix('order')->name('customer.')->group(function () {
    Route::get('/{ma_ban}',                    [QrController::class,    'scan']             )->name('scan');
    Route::get('/{ma_ban}/menu',               [MenuController::class,  'customerMenu']     )->name('menu');
    Route::post('/{ma_ban}/create',            [OrderController::class, 'createFromQr']     )->name('create');
    Route::get('/{ma_order}/cart',             [OrderController::class, 'showCart']         )->name('cart');
    Route::post('/{ma_order}/item',            [OrderController::class, 'addItem']          )->name('addItem');
    Route::delete('/{ma_order}/item/{ma_mon}', [OrderController::class, 'removeItem']       )->name('removeItem');
    Route::post('/{ma_order}/confirm',         [OrderController::class, 'confirmByCustomer'])->name('confirm');
    Route::get('/{ma_order}/status',           [OrderController::class, 'status']           )->name('status');
    Route::get('/{ma_order}/status.json',      [OrderController::class, 'statusJson']       )->name('statusJson');
});

// ── AUTH ──────────────────────────────────────────────────────
Route::get( '/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login']    )->name('login.post');
Route::post('/logout', [AuthController::class, 'logout']   )->name('logout');

// ── STAFF ─────────────────────────────────────────────────────
Route::middleware(['auth.staff'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/',                      [OrderController::class, 'index']       )->name('index');
        Route::get('/api/list',              [OrderController::class, 'apiList']     )->name('api.list');
        Route::get('/{ma_order}',            [OrderController::class, 'show']        )->name('show');
        Route::put('/{ma_order}/confirm',    [OrderController::class, 'confirm']     )->name('confirm');
        Route::put('/{ma_order}/status',     [OrderController::class, 'updateStatus'])->name('status');
        Route::post('/{ma_order}/merge',     [OrderController::class, 'merge']       )->name('merge');
        Route::post('/{ma_order}/split',     [OrderController::class, 'split']       )->name('split');
    });

    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get( '/{ma_order}', [PaymentController::class, 'show']   )->name('show');
        Route::post('/{ma_order}', [PaymentController::class, 'process'])->name('process');
    });

    Route::middleware(['role:quan_ly'])->prefix('menu')->name('menu.')->group(function () {
        Route::get('/',              [MenuController::class, 'index']  )->name('index');
        Route::get('/create',        [MenuController::class, 'create'] )->name('create');
        Route::post('/',             [MenuController::class, 'store']  )->name('store');
        Route::get('/{ma_mon}/edit', [MenuController::class, 'edit']   )->name('edit');
        Route::put('/{ma_mon}',      [MenuController::class, 'update'] )->name('update');
        Route::delete('/{ma_mon}',   [MenuController::class, 'destroy'])->name('destroy');
    });

    Route::get('/ban',             [BanController::class, 'index']   )->name('ban.index');
    Route::get('/ban/{ma_ban}/qr', [QrController::class, 'generate'] )->name('ban.qr');

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/',      [InventoryController::class, 'index']   )->name('index');
        Route::get('/alert', [InventoryController::class, 'lowStock'])->name('alert');
        Route::resource('materials',  NguyenLieuController::class);
        Route::resource('import',     ImportController::class);
        Route::put('/import/{id}/approve',     [ImportController::class,    'approve'])->name('import.approve');
        Route::put('/import/{id}/cancel',      [ImportController::class,    'cancel'] )->name('import.cancel');
        Route::resource('supplier',   SupplierController::class);
        Route::resource('stockcheck', StockCheckController::class);
        Route::put('/stockcheck/{id}/confirm', [StockCheckController::class,'confirm'])->name('stockcheck.confirm');
        Route::get('/report',        [ReportController::class, 'index'] )->name('report');
        Route::get('/report/export', [ReportController::class, 'export'])->name('report.export');
    });
});
