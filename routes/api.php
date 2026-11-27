<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Kantinku UBBG
|--------------------------------------------------------------------------
| Semua response memakai envelope {status, message, data} + meta pagination.
| Endpoint publik: katalog menu & kategori.
| Endpoint privat: butuh Bearer token Sanctum.
*/

Route::get('/', fn () => ApiResponse::success([
    'name' => 'Kantinku UBBG API',
    'version' => '1.0.0',
    'docs' => '/docs/testing_results.md',
], 'API aktif.'));

// ---------- Autentikasi ----------
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::put('password', [AuthController::class, 'changePassword']);
    });
});

// ---------- Katalog publik ----------
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('categories/{category}/menus', [CategoryController::class, 'menus']);

Route::get('menus', [MenuController::class, 'index']);
Route::get('menus/{menu}', [MenuController::class, 'show']);

// ---------- Butuh login ----------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/summary', [OrderController::class, 'summary']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::get('orders/{order}/receipt', [OrderController::class, 'receipt']);
    Route::post('orders/{order}/items', [OrderController::class, 'addItem']);
    Route::put('orders/{order}/items/{item}', [OrderController::class, 'updateItem']);
    Route::delete('orders/{order}/items/{item}', [OrderController::class, 'removeItem']);
    Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::delete('orders/{order}', [OrderController::class, 'destroy']);

    // ---------- Khusus admin ----------
    Route::middleware('admin')->group(function () {
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

        Route::post('menus', [MenuController::class, 'store']);
        Route::put('menus/{menu}', [MenuController::class, 'update']);
        Route::delete('menus/{menu}', [MenuController::class, 'destroy']);

        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
    });
});
