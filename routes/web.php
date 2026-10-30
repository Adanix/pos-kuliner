<?php

use App\Http\Controllers\AiImportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WizardController;
use Illuminate\Support\Facades\Route;

// Public Informational & Landing Routes
Route::get('/', [HomeController::class, 'landing'])->name('home');
Route::get('/docs', [HomeController::class, 'docs'])->name('docs');

// Public Auth & Demo Login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::match(['get', 'post'], '/login/demo', [AuthController::class, 'demoLogin'])->name('login.demo');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/pin/verify', [AuthController::class, 'verifyPin'])->name('pin.verify')->middleware('auth');

// Protected Routes (Must be logged in)
Route::middleware('auth')->group(function () {

    // Setup Wizard (Owner only)
    Route::middleware('role:owner')->group(function () {
        Route::get('/mulai', [WizardController::class, 'index'])->name('wizard.index');
        Route::post('/mulai', [WizardController::class, 'saveStep'])->name('wizard.save');
    });

    // POS & Cashier Operations (Owner & Cashier)
    Route::middleware('role:cashier,owner')->group(function () {
        Route::get('/kasir', [PosController::class, 'index'])->name('pos.index');
        Route::post('/transaksi', [PosController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('transactions.store');

        // Cashier Shift (Buka / Tutup Kas)
        Route::get('/kasir/shift', [ShiftController::class, 'index'])->name('pos.shift.index');
        Route::post('/kasir/shift/buka', [ShiftController::class, 'open'])->name('pos.shift.open');
        Route::post('/kasir/shift/tutup', [ShiftController::class, 'close'])->name('pos.shift.close');

        // Transaction History & Receipts
        Route::get('/transaksi', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transaksi/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('/transaksi/{transaction}/struk', [ReceiptController::class, 'show'])->name('transactions.receipt');

        // AI Import Operations (Owner & Cashier)
        Route::get('/impor', [AiImportController::class, 'index'])->name('impor.index');
        Route::post('/impor', [AiImportController::class, 'store'])->name('impor.store');
        Route::get('/impor/{aiImport}', [AiImportController::class, 'review'])->name('impor.review');
        Route::get('/impor/{aiImport}/gambar', [AiImportController::class, 'image'])->name('impor.image');
        Route::post('/impor/{aiImport}/simpan', [AiImportController::class, 'saveTransaction'])->name('impor.save');
    });

    // Owner Operations (Owner only)
    Route::middleware('role:owner')->group(function () {
        // Owner Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Void Transaction
        Route::post('/transaksi/{transaction}/void', [TransactionController::class, 'void'])->name('transactions.void');

        // Master Data: Categories
        Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Master Data: Menus
        Route::resource('menu', MenuController::class);

        // Master Data: Ingredients (Bahan Baku)
        Route::resource('bahan', IngredientController::class);

        // Master Data: Recipes (Resep)
        Route::get('/resep', [RecipeController::class, 'index'])->name('resep.index');
        Route::get('/resep/{menu}', [RecipeController::class, 'edit'])->name('resep.edit');
        Route::put('/resep/{menu}', [RecipeController::class, 'update'])->name('resep.update');

        // Inventory / Stock
        Route::get('/stok', [StockController::class, 'index'])->name('stok.index');
        Route::post('/stok/beli', [StockController::class, 'purchase'])->name('stok.purchase');
        Route::post('/stok/waste', [StockController::class, 'waste'])->name('stok.waste');
        Route::get('/stok/opname', [StockController::class, 'showOpname'])->name('stok.opname');
        Route::post('/stok/opname', [StockController::class, 'storeOpname'])->name('stok.opname.store');

        // Reports & CSV Export
        Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/export', [ReportController::class, 'export'])->name('reports.export');

        // Settings & User Management
        Route::get('/pengaturan', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/pengaturan', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/pengaturan/users', [SettingController::class, 'storeUser'])->name('settings.users.store');
        Route::post('/pengaturan/users/{user}/toggle', [SettingController::class, 'toggleUserActive'])->name('settings.users.toggle');
    });
});
