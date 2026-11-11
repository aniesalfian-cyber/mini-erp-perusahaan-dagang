<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackorderController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryOrderController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Public & Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick-login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (Must be logged in)
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modul Master Data Barang (Semua divisi dapat melihat, Admin/Purchasing dapat mengelola)
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    Route::middleware(['role:ADMIN|PURCHASING'])->group(function () {
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    });
    Route::middleware(['role:ADMIN'])->group(function () {
        Route::post('/items/{item}/adjust', [ItemController::class, 'adjustStock'])->name('items.adjust');
    });

    // Modul Pelanggan & Pemasok
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::middleware(['role:ADMIN|MARKETING'])->group(function () {
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    });

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::middleware(['role:ADMIN|PURCHASING'])->group(function () {
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    });

    // Modul Siklus Penjualan (Marketing & Admin)
    Route::get('/sales-orders', [SalesOrderController::class, 'index'])->name('sales-orders.index');
    Route::get('/sales-orders/{salesOrder}', [SalesOrderController::class, 'show'])->name('sales-orders.show');
    Route::get('/sales-orders/{salesOrder}/print', [SalesOrderController::class, 'print'])->name('sales-orders.print');
    Route::middleware(['role:ADMIN|MARKETING'])->group(function () {
        Route::post('/sales-orders', [SalesOrderController::class, 'store'])->name('sales-orders.store');
        Route::post('/sales-orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])->name('sales-orders.cancel');
    });

    // Laporan Khusus: Barang PO Belum Dikirimkan (Outstanding Backorder)
    Route::get('/backorders', [BackorderController::class, 'index'])->name('backorders.index');
    Route::get('/backorders/export-csv', [BackorderController::class, 'exportCsv'])->name('backorders.export-csv');

    // Modul Gudang & Surat Jalan (Logistik & Admin)
    Route::get('/delivery-orders', [DeliveryOrderController::class, 'index'])->name('delivery-orders.index');
    Route::get('/delivery-orders/{deliveryOrder}', [DeliveryOrderController::class, 'show'])->name('delivery-orders.show');
    Route::get('/delivery-orders/{deliveryOrder}/print', [DeliveryOrderController::class, 'print'])->name('delivery-orders.print');
    Route::middleware(['role:ADMIN|LOGISTIK'])->group(function () {
        Route::post('/delivery-orders', [DeliveryOrderController::class, 'store'])->name('delivery-orders.store');
        Route::post('/delivery-orders/{deliveryOrder}/status', [DeliveryOrderController::class, 'updateStatus'])->name('delivery-orders.status');
    });

    // Modul Siklus Pengadaan (Purchasing & Admin)
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::get('/purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
    Route::middleware(['role:ADMIN|PURCHASING'])->group(function () {
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    });

    // Modul Penerimaan Barang / GRN (Logistik & Admin)
    Route::get('/goods-receipts', [GoodsReceiptController::class, 'index'])->name('goods-receipts.index');
    Route::get('/goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->name('goods-receipts.show');
    Route::get('/goods-receipts/{goodsReceipt}/print', [GoodsReceiptController::class, 'print'])->name('goods-receipts.print');
    Route::middleware(['role:ADMIN|LOGISTIK'])->group(function () {
        Route::post('/goods-receipts', [GoodsReceiptController::class, 'store'])->name('goods-receipts.store');
    });

    // Modul Keuangan & Faktur Penjualan (Accounting & Admin)
    Route::get('/invoices', [SalesInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [SalesInvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [SalesInvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/print', [SalesInvoiceController::class, 'print'])->name('invoices.print');
    Route::middleware(['role:ADMIN|ACCOUNTING'])->group(function () {
        Route::post('/invoices/from-do', [SalesInvoiceController::class, 'createFromDO'])->name('invoices.create-from-do');
        Route::post('/invoices/{invoice}/payment', [SalesInvoiceController::class, 'recordPayment'])->name('invoices.payment');
    });

    // Modul Audit Trail (Admin / Owner)
    Route::middleware(['role:ADMIN'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
