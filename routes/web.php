<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpeditionController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// Redirect root ke admin
Route::get('/', fn () => redirect()->route('admin.dashboard'));

// ─── Admin Auth (tanpa middleware) ───────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',  [AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ─── Admin Panel (dengan middleware admin) ───────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Users Management ──────────────────────────────────────────────────────
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('superadmin')
        ->name('users.destroy');

    // ── Categories ────────────────────────────────────────────────────────────
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggleActive'])->name('categories.toggle');

    // ── Products ──────────────────────────────────────────────────────────────
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggleActive'])->name('products.toggle');
    // ── Banners ──────────────────────────────────────────────────────────────
    Route::resource('banners', App\Http\Controllers\Admin\BannerController::class)->names('banners');
    // ── Expeditions ───────────────────────────────────────────────────────────
    Route::get('/expeditions', [ExpeditionController::class, 'index'])->name('expeditions.index');
    Route::get('/expeditions/create', [ExpeditionController::class, 'create'])->name('expeditions.create');
    Route::post('/expeditions', [ExpeditionController::class, 'store'])->name('expeditions.store');
    Route::get('/expeditions/{expedition}/edit', [ExpeditionController::class, 'edit'])->name('expeditions.edit');
    Route::put('/expeditions/{expedition}', [ExpeditionController::class, 'update'])->name('expeditions.update');
    Route::delete('/expeditions/{expedition}', [ExpeditionController::class, 'destroy'])->name('expeditions.destroy');
    Route::patch('/expeditions/{expedition}/toggle', [ExpeditionController::class, 'toggleActive'])->name('expeditions.toggle');

    // ── Orders ────────────────────────────────────────────────────────────────
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('/orders/{order}/resi', [OrderController::class, 'updateResi'])->name('orders.resi');
    Route::post('/orders/{order}/track', [OrderController::class, 'trackWaybill'])->name('orders.track');

    // ── Payments ──────────────────────────────────────────────────────────────
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');

    // ── Settings ──────────────────────────────────────────────────────────────
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // ── Stock Movements ───────────────────────────────────────────────────────
    Route::get('/stock-movements/pdf', [StockMovementController::class, 'downloadPdf'])->name('stock-movements.pdf');
    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
    Route::post('/stock-movements', [StockMovementController::class, 'store'])->name('stock-movements.store');

    Route::get('/list-ongkir', function () {
        try {
            $response = Http::withoutVerifying()->timeout(5)->withHeaders([
                'key' => '4TSna80Qc7b078835126723c6Prhqa8C'
            ])->get('https://api.rajaongkir.com/starter/province');
            if ($response->failed()) {
                throw new \Exception('API request failed');
            }
            dd($response->json());
        } catch (\Exception $e) {
            dd([
                'message' => 'Connection timed out, showing mock provinces fallback.',
                'rajaongkir' => [
                    'status' => ['code' => 200, 'description' => 'OK (Mocked due to Connection Timeout)'],
                    'results' => [
                        ['province_id' => '6', 'province' => 'DKI Jakarta'],
                        ['province_id' => '9', 'province' => 'Jawa Barat'],
                        ['province_id' => '10', 'province' => 'Jawa Tengah'],
                        ['province_id' => '11', 'province' => 'Jawa Timur'],
                        ['province_id' => '5', 'province' => 'DI Yogyakarta'],
                    ]
                ]
            ]);
        }
    });

    // ── Chat Customer ────────────────────────────────────────────────────────
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/chats/{chat}', [ChatController::class, 'show'])->name('chats.show');
    Route::post('/chats/{chat}/reply', [ChatController::class, 'reply'])->name('chats.reply');
    Route::post('/chats/{chat}/close', [ChatController::class, 'close'])->name('chats.close');
    Route::post('/chats/{chat}/reopen', [ChatController::class, 'reopen'])->name('chats.reopen');
    Route::get('/chats-unread-count', [ChatController::class, 'unreadCount'])->name('chats.unread-count');
});


// Rute verifikasi email via signed URL
Route::get('/verify-email/{id}/{hash}', function ($id, $hash) {
    $user = \App\Models\User::findOrFail($id);

    if (! hash_equals(sha1($user->email), (string) $hash)) {
        abort(403);
    }

    $user->update([
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    return view('auth.verified', ['name' => $user->name]);
})->middleware(['signed'])->name('verification.verify');