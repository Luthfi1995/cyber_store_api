<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CustomerAddressController;
use App\Http\Controllers\Api\ExpeditionController;
use App\Http\Controllers\Api\InfoController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\MidtransCallbackController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/google', [GoogleAuthController::class, 'loginWithGoogle']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/expeditions', [ExpeditionController::class, 'index']);
    Route::get('/about', [InfoController::class, 'about']);
    Route::get('/help', [InfoController::class, 'help']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/profile', [ProfileController::class, 'update']);

        Route::apiResource('/addresses', CustomerAddressController::class);

        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/add', [CartController::class, 'add']);
        Route::patch('/cart/items/{itemId}', [CartController::class, 'update']);
        Route::delete('/cart/items/{itemId}', [CartController::class, 'remove']);
        Route::delete('/cart', [CartController::class, 'clear']);

        Route::post('/checkout', [CheckoutController::class, 'store']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders/{order}/complete', [OrderController::class, 'complete']);

        // Khusus testing lokal. Di production, endpoint ini diganti callback payment gateway/VA bank.
        Route::post('/payments/{payment}/simulate-paid', [PaymentController::class, 'simulatePaid']);
    });

    // Public Midtrans Callback Webhook
    Route::post('/payments/midtrans-callback', [MidtransCallbackController::class, 'handle']);
});
