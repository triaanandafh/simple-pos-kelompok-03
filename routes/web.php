
<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Redirect halaman utama ke POS
Route::get('/', function () {
    return redirect()->route('pos.create');
});

// Route untuk Guest (Belum Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// Halaman info khusus tamu
Route::get('/info', function () {
    return view('info');
})->middleware('guest')->name('info');

// Route untuk User yang Sudah Login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');

    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    // Riwayat transaksi: Admin, Kasir, dan Manager
    Route::middleware('role:admin,manager,kasir')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])
            ->name('transactions.index');

        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])
            ->name('transactions.show');
    });

    // Kasir POS: hanya Admin dan Kasir
    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/pos', [TransactionController::class, 'create'])
            ->name('pos.create');

        Route::post('/pos', [TransactionController::class, 'store'])
            ->name('transactions.store');
    });

    // Route khusus Admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class)
            ->except('show');

        Route::resource('products', ProductController::class)
            ->except(['show', 'destroy']);

        // Laporan penjualan
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        // Export laporan
        Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])
            ->name('reports.export.csv');

        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])
            ->name('reports.export.pdf');

        Route::get('/reports/export/xlsx', [ReportController::class, 'exportExcel'])
            ->name('reports.export.xlsx');

        // Import produk dari CSV
        Route::get('/import', [ReportController::class, 'importForm'])
            ->name('import.create');

        Route::post('/import', [ReportController::class, 'import'])
            ->name('import.store');
    });
});
