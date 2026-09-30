<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('master')->name('master.')->group(function () {
        foreach (['users', 'coa', 'kategori', 'lokasi', 'barang', 'supplier', 'customer', 'erp'] as $type) {
            Route::get("/{$type}", [MasterController::class, 'index'])->defaults('type', $type)->name($type);
            Route::post("/{$type}", [MasterController::class, 'store'])->defaults('type', $type)->name("{$type}.store");
            Route::delete("/{$type}/{id}", [MasterController::class, 'destroy'])->defaults('type', $type)->name("{$type}.destroy");
        }
        Route::post('/users/roles', [MasterController::class, 'assignUserRoles'])->name('users.roles');
        Route::post('/users/password', [MasterController::class, 'resetUserPassword'])->name('users.password');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/roles', [SettingsController::class, 'roles'])->name('roles');
        Route::post('/roles', [SettingsController::class, 'storeRole'])->name('roles.store');
        Route::delete('/roles/{role}', [SettingsController::class, 'deleteRole'])->name('roles.delete');
        Route::get('/menus', [SettingsController::class, 'menus'])->name('menus');
        Route::get('/menus/data/{role}', [SettingsController::class, 'menuData'])->name('menus.data');
        Route::put('/menus/access', [SettingsController::class, 'updateMenuAccess'])->name('menus.access');
        Route::post('/menus', [SettingsController::class, 'storeMenu'])->name('menus.store');
        Route::delete('/menus/{menu}', [SettingsController::class, 'deleteMenu'])->name('menus.delete');
        Route::get('/company', [SettingsController::class, 'company'])->name('company');
        Route::post('/company', [SettingsController::class, 'updateCompany'])->name('company.update');
    });
    Route::prefix('setting')->name('setting.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/', [SettingController::class, 'update'])->name('update');
    });

    Route::prefix('pembelian')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseController::class, 'create'])->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::get('/{purchase}/print', [PurchaseController::class, 'print'])->name('print');
        Route::get('/{purchase}/logs', [PurchaseController::class, 'logs'])->name('logs');
    });
    Route::prefix('penjualan')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/create', [SaleController::class, 'create'])->name('create');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('/{sale}/print', [SaleController::class, 'print'])->name('print');
        Route::get('/{sale}/logs', [SaleController::class, 'logs'])->name('logs');
    });
    Route::prefix('invoice')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/print', [InvoiceController::class, 'print'])->name('print');
        Route::post('/{invoice}/payment', [InvoiceController::class, 'payment'])->name('payment');
    });
    Route::prefix('surat-jalan')->name('delivery.')->group(function () {
        Route::get('/', [DeliveryOrderController::class, 'index'])->name('index');
        Route::get('/from-invoice/{invoice}', [DeliveryOrderController::class, 'create'])->name('create');
        Route::post('/from-invoice/{invoice}', [DeliveryOrderController::class, 'store'])->name('store');
        Route::get('/{deliveryOrder}', [DeliveryOrderController::class, 'show'])->name('show');
    });
    Route::get('/penerimaan', [CashTransactionController::class, 'index'])->defaults('direction', 'in')->name('cash.in');
    Route::get('/penerimaan/{transaction}/print', [CashTransactionController::class, 'print'])->defaults('direction', 'in')->name('cash.in.print');
    Route::post('/penerimaan', [CashTransactionController::class, 'store'])->defaults('direction', 'in')->name('cash.in.store');
    Route::get('/pengeluaran', [CashTransactionController::class, 'index'])->defaults('direction', 'out')->name('cash.out');
    Route::get('/pengeluaran/{transaction}/print', [CashTransactionController::class, 'print'])->defaults('direction', 'out')->name('cash.out.print');
    Route::post('/pengeluaran', [CashTransactionController::class, 'store'])->defaults('direction', 'out')->name('cash.out.store');

    Route::prefix('laporan')->name('reports.')->group(function () {
        Route::get('/stok', [ReportController::class, 'stock'])->name('stock');
        Route::get('/kartu-stok', [ReportController::class, 'movements'])->name('movements');
        Route::get('/arus-kas', [ReportController::class, 'cashflow'])->name('cashflow');
        Route::get('/log-perubahan', [ReportController::class, 'logs'])->name('logs');
        Route::get('/laba-rugi', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/neraca', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/piutang', [ReportController::class, 'receivables'])->name('receivables');
        Route::get('/hutang', [ReportController::class, 'payables'])->name('payables');
        Route::get('/{report}/export/{format}', [ReportExportController::class, 'export'])->name('export');
    });
});
