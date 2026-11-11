<?php

use App\Http\Controllers\Account\DashboardController as AccountDashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SellerApplicationController as AdminSellerApplicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Seller\ApplicationController as SellerApplicationController;
use App\Http\Controllers\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Storefront\CartItemController;
use App\Http\Controllers\Storefront\CartPageController;
use App\Http\Controllers\Storefront\CategoryPageController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductMediaController;
use App\Http\Controllers\Storefront\ProductPageController;
use App\Http\Controllers\Storefront\SearchSuggestionController;
use App\Http\Controllers\Storefront\SellerStoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('arama/oneri', SearchSuggestionController::class)
    ->middleware('throttle:60,1')
    ->name('search.suggestions');
Route::get('magaza/{slug}', SellerStoreController::class)->name('sellers.show');
Route::get('kategori/{slug}', CategoryPageController::class)->name('categories.show');
Route::get('urun/{slug}', ProductPageController::class)->name('products.show');
Route::post('urun/{product:slug}/gorseller', [ProductMediaController::class, 'store'])
    ->middleware('auth')
    ->name('products.media.store');
Route::get('sepet', CartPageController::class)->name('cart.show');
Route::post('cart/items', [CartItemController::class, 'store'])->name('cart.items.store');
Route::get('satici-basvurusu', [SellerApplicationController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('seller.application.create');
Route::post('satici-basvurusu', [SellerApplicationController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('seller.application.store');

Route::get('dashboard', AccountDashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('categories', CategoryController::class)
            ->except(['show']);

        Route::get('seller-applications', [AdminSellerApplicationController::class, 'index'])
            ->name('seller-applications.index');
        Route::patch('seller-applications/{seller}/approve', [AdminSellerApplicationController::class, 'approve'])
            ->name('seller-applications.approve');
        Route::patch('seller-applications/{seller}/reject', [AdminSellerApplicationController::class, 'reject'])
            ->name('seller-applications.reject');
    });

Route::get('satici', SellerDashboardController::class)
    ->middleware(['auth', 'verified', 'role:seller', 'approved.seller'])
    ->name('seller.dashboard');

Route::middleware(['auth', 'verified', 'role:seller', 'approved.seller'])
    ->prefix('satici')
    ->name('seller.')
    ->group(function (): void {
        Route::patch('urunler/{product}/durum', [SellerProductController::class, 'updateStatus'])
            ->name('products.status');
        Route::resource('urunler', SellerProductController::class)
            ->parameters(['urunler' => 'product'])
            ->except(['show'])
            ->names('products');
    });

Route::middleware('auth')->group(function (): void {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
