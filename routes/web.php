<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicalSpecialtyController;
use App\Http\Controllers\RepresentativeController;
use App\Http\Controllers\CicloController;
use App\Http\Controllers\DetalleCicloController;
use App\Http\Middleware\CheckUserActive;
use App\Http\Middleware\SuperAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', CheckUserActive::class])
    ->name('dashboard');

Route::middleware(['auth', CheckUserActive::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de productos
    Route::resource('products', ProductController::class);

    // Rutas de especialidades médicas
    Route::get('/medical-specialties', [MedicalSpecialtyController::class, 'index'])->name('medical-specialties.index');
    Route::post('/medical-specialties', [MedicalSpecialtyController::class, 'store'])->name('medical-specialties.store');
    Route::delete('/medical-specialties/{medicalSpecialty}', [MedicalSpecialtyController::class, 'destroy'])->name('medical-specialties.destroy');

    // Rutas de representantes
    Route::resource('representatives', RepresentativeController::class);

    // Rutas de ciclos
    Route::resource('ciclos', CicloController::class);
    Route::get('/ciclos/{ciclo}/pdf', [CicloController::class, 'generatePdf'])->name('ciclos.pdf');
    Route::get('/ciclos/{ciclo}/invoice', [CicloController::class, 'generateInvoice'])->name('ciclos.invoice');
    Route::get('/ciclos/{ciclo}/reporte', [CicloController::class, 'generarReporte'])->name('ciclos.reporte');
    Route::get('/ciclos/{ciclo}/excel', [CicloController::class, 'exportToExcel'])->name('ciclos.excel');
    Route::get('/ciclos/{ciclo}/configuracion', [CicloController::class, 'getConfiguracion'])->name('ciclos.configuracion');
    Route::get('/ciclos/{ciclo}/configuracion-anterior', [CicloController::class, 'getConfiguracionAnterior'])->name('ciclos.configuracion-anterior');
    Route::get('/ciclos/configuracion/{nombre}', [CicloController::class, 'getConfiguracionPorNombre'])->name('ciclos.configuracion-por-nombre');
    Route::put('/ciclos/{ciclo}/deliver', [CicloController::class, 'deliver'])->name('ciclos.deliver');
    Route::put('/ciclos/{ciclo}/completar-entrega', [CicloController::class, 'completarEntrega'])->name('ciclos.completar-entrega');
    Route::patch('/ciclos/{ciclo}/update-descargo', [CicloController::class, 'updateDescargo'])->name('ciclos.update-descargo');
    Route::get('/ciclos/por-año/{año}', [CicloController::class, 'getCiclosPorAño'])->name('ciclos.por-año');
});

Route::middleware(['auth'])->group(function () {
    Route::patch('/detalle-ciclos/update-descargo', [DetalleCicloController::class, 'updateNumeroDescargo'])
        ->name('detalle-ciclos.update-descargo');
});

// Rutas de administración
Route::group(['middleware' => ['auth', CheckUserActive::class, SuperAdmin::class], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/approve', [UserManagementController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
