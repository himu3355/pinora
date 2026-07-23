<?php

use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VendorRegistrationController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ShopController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CartController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Shop
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/category/{slug}', [ShopController::class, 'byCategory'])->name('shop.category');
Route::get('/vendor/{slug}/shop', [ShopController::class, 'byVendor'])->name('shop.vendor');

// Vendors Directory & Storefront
Route::get('/vendors', [\App\Http\Controllers\Frontend\VendorController::class, 'index'])->name('vendors.index');
Route::get('/vendors/{slug}', [\App\Http\Controllers\Frontend\VendorController::class, 'show'])->name('vendors.show');

// Product Detail
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::post('/product/{id}/review', [ProductController::class, 'storeReview'])->name('product.review.store')->middleware('auth');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

// Wishlist toggle (used on product cards & account page)
Route::post('/wishlist/toggle/{productId}', [\App\Http\Controllers\Frontend\AccountController::class, 'toggleWishlist'])->name('wishlist.toggle')->middleware('auth');

Route::get('/admin/exit-impersonation', function () {
    $adminId = session()->pull('impersonating_admin_id');
    if ($adminId) {
        auth()->loginUsingId($adminId);
    }
    return redirect()->route('filament.admin.pages.dashboard');
})->name('admin.impersonation.exit')->middleware('auth');

Route::middleware('guest')->group(function () {
    Route::get('register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('register', [CustomerAuthController::class, 'register']);

    Route::get('login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [CustomerAuthController::class, 'login']);

    // Vendor self-registration temporarily disabled
    Route::get('register/vendor', fn() => redirect()->route('login'))->name('vendor.apply');
    Route::post('register/vendor', fn() => redirect()->route('login'))->name('vendor.apply.submit');

    Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\OrderController;

Route::middleware('auth')->group(function () {
    Route::post('logout', [CustomerAuthController::class, 'logout'])->name('logout');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/orders/{orderNumber}/confirmation', [OrderController::class, 'confirmation'])->name('order.confirmation');
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('order.show');

    // Customer Account Routes
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Frontend\AccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [\App\Http\Controllers\Frontend\AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [\App\Http\Controllers\Frontend\AccountController::class, 'orderDetail'])->name('orders.show');
        Route::get('/wishlist', [\App\Http\Controllers\Frontend\AccountController::class, 'wishlist'])->name('wishlist');
        Route::delete('/wishlist/remove/{productId}', [\App\Http\Controllers\Frontend\AccountController::class, 'wishlistDestroy'])->name('wishlist.remove');
        
        Route::get('/addresses', [\App\Http\Controllers\Frontend\AccountController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [\App\Http\Controllers\Frontend\AccountController::class, 'addressStore'])->name('addresses.store');
        Route::put('/addresses/{address}', [\App\Http\Controllers\Frontend\AccountController::class, 'addressUpdate'])->name('addresses.update');
        Route::delete('/addresses/{address}', [\App\Http\Controllers\Frontend\AccountController::class, 'addressDestroy'])->name('addresses.destroy');
        Route::post('/addresses/{address}/default', [\App\Http\Controllers\Frontend\AccountController::class, 'addressSetDefault'])->name('addresses.default');
        
        Route::get('/profile', [\App\Http\Controllers\Frontend\AccountController::class, 'profile'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\Frontend\AccountController::class, 'profileUpdate'])->name('profile.update');
    });
});

// Stripe Webhook
Route::post('/webhooks/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');


