<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController; // Pastikan path ini benar
use App\Http\Controllers\Api\Admin\SettingController;   // Pastikan path ini benar
use App\Http\Controllers\Api\Admin\ProfileController; // Pastikan path ini benar

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all ofthem will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route ini biasanya untuk mendapatkan user yang terotentikasi melalui cookie (SPA)
// atau token jika guard 'sanctum' bekerja pada /api/user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Admin routes yang diproteksi
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/dashboard-summary', [DashboardController::class, 'summary'])->name('admin.dashboard.summary');
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [SettingController::class, 'store'])->name('admin.settings.store');
    Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::post('/admin/profile/change-password', [ProfileController::class, 'changePassword'])->name('admin.profile.changePassword');
    // Jika operasi CRUD Batch (selain view publik) butuh otentikasi, pindahkan ke sini
    Route::post('/batches', [BatchController::class, 'store'])->name('admin.batches.store');
    Route::put('/batches/{batch}', [BatchController::class, 'update'])->name('admin.batches.update');
    // PATCH juga bisa diarahkan ke update jika Anda ingin lebih RESTful
    Route::patch('/batches/{batch}', [BatchController::class, 'update']);
    Route::delete('/batches/{batch}', [BatchController::class, 'destroy'])->name('admin.batches.destroy');
});

// Rute publik
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::get('/batches', [BatchController::class, 'index'])->name('batches.index.public');
Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show.public');


// Hapus apiResource jika Anda sudah mendefinisikan semua method BatchController secara manual
// Route::apiResource('batches', BatchController::class);
// Jika Anda ingin tetap menggunakan apiResource tapi mengecualikan yang sudah didefinisikan:
// Route::apiResource('batches', BatchController::class)->except(['index', 'show', 'store', 'update', 'destroy']);

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from API']);
});
