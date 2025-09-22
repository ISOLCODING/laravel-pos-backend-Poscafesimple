<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// post login
Route::post('login', [AuthController::class, 'login']);

// post logout
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// api resource product
Route::prefix('products')->group(function () {
    // Basic CRUD operations
    Route::get('/', [ProductController::class, 'index']); // GET //products
    Route::get('/{id}', [ProductController::class, 'show']); // GET //products/{id}
    Route::post('/', [ProductController::class, 'store']); // POST //products
    Route::put('/{id}', [ProductController::class, 'update']); // PUT //products/{id}
    Route::patch('/{id}', [ProductController::class, 'update']); // PATCH //products/{id}
    Route::delete('/{id}', [ProductController::class, 'destroy']); // DELETE /api/products/{id}
})->middleware('auth:sanctum');
// api resource order
Route::apiResource('orders', OrderController::class)->middleware('auth:sanctum');

// get categories
Route::get('list-categories', [CategoryController::class, 'index'])->middleware('auth:sanctum');

// api resource report
Route::get('/reports/summary', [ReportController::class, 'summary'])->middleware('auth:sanctum');
Route::get('/reports/product-sales', [ReportController::class, 'productSales'])->middleware('auth:sanctum');
Route::get('/reports/close-cashier', [ReportController::class, 'closeCashier'])->middleware('auth:sanctum');
