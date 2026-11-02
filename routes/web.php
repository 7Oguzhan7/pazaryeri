<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('categories', CategoryController::class)
            ->except(['show']);
    });

Route::view('satici', 'seller.dashboard')
    ->middleware(['auth', 'verified', 'role:seller', 'approved.seller'])
    ->name('seller.dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
