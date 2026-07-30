<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\PushDeviceTokenController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Support\Facades\Route;

Route::domain((string) config('api.domain'))
    ->prefix('v1')
    ->name('developer.v1.')
    ->middleware(['api.client', 'throttle:api-client'])
    ->group(function () {
        Route::get('home', [HomeController::class, 'index'])->name('home');
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{public_id}', [ProductController::class, 'show'])->name('products.show');
        Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('brands/{slug}', [BrandController::class, 'show'])->name('brands.show');
        Route::get('campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    });

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Auth Routes
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login');

        Route::middleware(['auth:sanctum', 'abilities:mobile'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('user', [AuthController::class, 'user'])->name('user');
        });
    });

    Route::middleware(['auth:sanctum', 'abilities:mobile'])->group(function () {
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        // Cart Routes
        Route::get('cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('cart', [CartController::class, 'store'])->name('cart.store');
        Route::patch('cart/{public_id}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('cart/{public_id}', [CartController::class, 'destroy'])->name('cart.destroy');

        // Checkout Routes
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        // Address Routes
        Route::apiResource('addresses', AddressController::class);

        // Order Routes
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{public_id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{public_id}/payment', [PaymentController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('orders.payment.store');

        // Notification Routes
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::patch('notifications/{public_id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('push-device-tokens', [PushDeviceTokenController::class, 'store'])->name('push-device-tokens.store');
        Route::delete('push-device-tokens', [PushDeviceTokenController::class, 'destroy'])->name('push-device-tokens.destroy');

        // Review Route
        Route::post('products/{public_id}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        Route::get('wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::put('wishlist/{public_id}', [WishlistController::class, 'store'])->name('wishlist.store');
        Route::delete('wishlist/{public_id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    });

    // Public Routes
    Route::get('home', [HomeController::class, 'index'])->name('home');

    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{public_id}', [ProductController::class, 'show'])->name('products.show');

    Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('brands/{slug}', [BrandController::class, 'show'])->name('brands.show');

    Route::get('campaigns', [CampaignController::class, 'index'])->name('campaigns.index');

    Route::prefix('admin')->name('admin.')
        ->middleware(['auth:sanctum', 'abilities:admin', 'api.permission:users.manage'])
        ->group(function () {
            Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
            Route::patch('users/{user}/access', [AdminUserController::class, 'updateAccess'])->name('users.access.update');
        });
});
