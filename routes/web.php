<?php

use App\Http\Controllers\Frontend\BrandController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/newsletter', [HomeController::class, 'newsletter'])->name('newsletter');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:parent_sku}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('brands.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
use App\Http\Controllers\Customer\AuthController as CustomerAuth;
use App\Http\Controllers\Customer\DashboardController as CustomerDash;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\ShopController;




Route::prefix('shop')->name('store.')->group(function () {
    Route::get('/products/{product}', [ShopController::class, 'show'])->name('product');
    Route::get('/variants/{variant}', [ShopController::class, 'variant'])->name('variant');

    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/add-bulk', [CartController::class, 'addBulk'])->name('cart.add.bulk');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{variant}', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::get('/checkout/zones', [CheckoutController::class, 'zones'])->name('checkout.zones');
    Route::get('/checkout/methods', [CheckoutController::class, 'methods'])->name('checkout.methods');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
});

Route::prefix('account')->name('customer.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerAuth::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerAuth::class, 'login'])->name('login.post');
        Route::post('/login/ajax', [CustomerAuth::class, 'loginAjax'])->name('login.ajax');
        Route::get('/register', [CustomerAuth::class, 'showRegister'])->name('register');
        Route::post('/register', [CustomerAuth::class, 'register'])->name('register.post');
        Route::post('/register/ajax', [CustomerAuth::class, 'registerAjax'])->name('register.ajax');
    });

    Route::middleware('auth:customer')->group(function () {
        Route::get('/dashboard', [CustomerDash::class, 'index'])->name('dashboard');
        Route::get('/orders', [CustomerDash::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [CustomerDash::class, 'orderDetail'])->name('order.detail');
        Route::get('/wishlist', [CustomerDash::class, 'wishlist'])->name('wishlist');
        Route::post('/wishlist/{product}/toggle', [CustomerDash::class, 'wishlistToggle'])->name('wishlist.toggle');
        Route::get('/profile', [CustomerDash::class, 'account'])->name('account');
        Route::post('/profile', [CustomerDash::class, 'updateAccount'])->name('account.update');
        Route::get('/addresses', [CustomerDash::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [CustomerDash::class, 'storeAddress'])->name('addresses.store');
        Route::delete('/addresses/{address}', [CustomerDash::class, 'destroyAddress'])->name('addresses.destroy');
        Route::post('/logout', [CustomerAuth::class, 'logout'])->name('logout');
    });
});
