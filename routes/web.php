<?php

use Illuminate\Support\Facades\Route;
// AuthController tidak lagi digunakan untuk login/logout di sini
// use App\Http\Controllers\Api\AuthController; 
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\ProfileController;

// Rute fallback untuk view utama (jika ada)
Route::get('/', function () {
    return view('welcome');
});

// Grup untuk semua rute API stateful (untuk SPA Anda)
Route::prefix('api')->group(function () {

    // Rute login & logout sekarang ditangani oleh Fortify.
    // Rute manual di bawah ini harus dihapus.
    // Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login');

    // Rute terproteksi yang memerlukan sesi login dari Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        // Rute logout juga sudah ditangani Fortify (endpoint POST /logout).
        // Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

        // Dashboard, Settings, Profile
        Route::get('/admin/dashboard-summary', [DashboardController::class, 'summary'])->name('admin.dashboard.summary');
        Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
        Route::post('/admin/settings', [SettingController::class, 'store'])->name('admin.settings.store');
        Route::post('/admin/profile/change-password', [ProfileController::class, 'changePassword'])->name('admin.profile.changePassword');

        // Rute CRUD Batch untuk admin
        Route::post('/batches', [BatchController::class, 'store'])->name('admin.batches.store');
        Route::put('/batches/{batch}', [BatchController::class, 'update'])->name('admin.batches.update');
        Route::patch('/batches/{batch}', [BatchController::class, 'update']);
        Route::delete('/batches/{batch}', [BatchController::class, 'destroy'])->name('admin.batches.destroy');
    });
});