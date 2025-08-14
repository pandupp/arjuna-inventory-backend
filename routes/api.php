<?php
Route::get('/test', function () {
    return response()->json(['message' => 'Halo dari Railway!']);
});
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import semua controller yang digunakan
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\StockAdditionController;
use App\Http\Controllers\Api\StockOutflowController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- RUTE PUBLIK (Tidak Perlu Login) ---
Route::post('/login', [AuthController::class, 'login']);


// --- RUTE YANG MEMERLUKAN LOGIN (Dilindungi Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Rute User Bawaan & Logout
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // Pencarian
    Route::get('/search', [SearchController::class, 'search']);

    // Item
    Route::get('items/{item}/stock-card', [ItemController::class, 'getStockCard']);
    Route::apiResource('items', ItemController::class);

    // Customer
    Route::apiResource('customers', CustomerController::class);

    // Invoice
    Route::apiResource('invoices', InvoiceController::class);
    Route::patch('/invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid']);

    // Stok
    Route::apiResource('stock-additions', StockAdditionController::class);
    Route::apiResource('stock-outflows', StockOutflowController::class)->only(['store', 'destroy']);

    // ✅ RUTE BARU UNTUK DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index']);


    // --- PENGAMANAN ROUTE USER MANAGEMENT ---

    // Grup ini hanya bisa diakses oleh user dengan hak akses 'manage-users' (Manager Operasional)
    Route::middleware('can:manage-users')->group(function () {
        // Melindungi aksi membuat, mengupdate, dan menghapus user
        Route::post('users', [UserController::class, 'store']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy']);
    });

    // Route ini dibiarkan di luar grup agar semua role bisa melihat daftar user
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{user}', [UserController::class, 'show']);

});
