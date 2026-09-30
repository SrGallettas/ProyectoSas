<?php

use App\Http\Controllers\AcceptBusinessInvitationController;
use App\Http\Controllers\ActiveBusinessController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessTeamController;
use App\Http\Controllers\CashClosureController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleController;
use App\Http\Middleware\EnsureActiveBusiness;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', InicioController::class)
    ->middleware(['auth', 'verified', EnsureActiveBusiness::class])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('businesses', BusinessController::class)->only(['index', 'create', 'store']);
    Route::post('/businesses/{business}/select', ActiveBusinessController::class)
        ->name('businesses.select');

    Route::resource('products', ProductController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->middleware([EnsureActiveBusiness::class, 'business.role:owner,manager']);
    Route::resource('customers', CustomerController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->middleware([EnsureActiveBusiness::class, 'business.role:owner,manager']);
    Route::resource('categories', CategoryController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->middleware([EnsureActiveBusiness::class, 'business.role:owner,manager']);
    Route::get('/sales/export', [SaleController::class, 'export'])->name('sales.export')->middleware(EnsureActiveBusiness::class);
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show'])->middleware(EnsureActiveBusiness::class);
    Route::resource('cash-closures', CashClosureController::class)->only(['index', 'store'])->middleware([EnsureActiveBusiness::class, 'business.role:owner,manager']);
    Route::get('/team', [BusinessTeamController::class, 'index'])->name('team.index')->middleware([EnsureActiveBusiness::class, 'business.role:owner']);
    Route::post('/team/invitations', [BusinessTeamController::class, 'store'])->name('team.invitations.store')->middleware(EnsureActiveBusiness::class);
    Route::get('/team/invitations/{token}', AcceptBusinessInvitationController::class)->name('team.invitations.accept');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
