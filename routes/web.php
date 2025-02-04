<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserManagementController;
use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\SuperAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', CheckUserActive::class])->name('dashboard');

Route::middleware(['auth', CheckUserActive::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de productos
    Route::resource('products', ProductController::class);
});

// Rutas de administración
Route::group(['middleware' => ['auth', CheckUserActive::class, SuperAdmin::class], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/approve', [UserManagementController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
