<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BatchController;

/*
|--------------------------------------------------------------------------
| Stateless API Routes
|--------------------------------------------------------------------------
|
| Rute-rute di sini bersifat stateless dan tidak menggunakan sesi atau cookie.
| Cocok untuk API publik yang bisa diakses oleh siapa saja.
| Middleware 'api' akan diterapkan pada rute-rute ini.
|
*/

// Rute yang tidak memerlukan otentikasi sesi
Route::get('/batches', [BatchController::class, 'index'])->name('batches.index.public');
Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show.public');

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from Stateless API']);
});

// Middleware auth:sanctum di sini akan mengharapkan otentikasi via Bearer Token,
// bukan cookie. Jadi, kita pindahkan /user ke grup 'web' juga.
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});