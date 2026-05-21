<?php

use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\ShopController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('store.shop');
});

Route::prefix('shop')->name('store.')->group(function () {
    Route::get('/', [ShopController::class, 'index'])->name('shop');
    Route::get('/products/{product}', [ShopController::class, 'show'])->name('product');
    Route::get('/variants/{variant}', [ShopController::class, 'variant'])->name('variant');

    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{variant}', [CartController::class, 'remove'])->name('cart.remove');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::get('/checkout/zones', [CheckoutController::class, 'zones'])->name('checkout.zones');
    Route::get('/checkout/methods', [CheckoutController::class, 'methods'])->name('checkout.methods');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
});
