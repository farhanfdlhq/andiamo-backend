<?php

namespace App\Http\Controllers\Api; // Sesuaikan namespace jika berbeda

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator; // Tambahkan ini
use App\Models\User; // Pastikan model User di-import
use Illuminate\Support\Facades\Log; // Tambahkan ini untuk logging

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Logging untuk debugging - AWAL
        Log::info('----------------------------------------------------');
        Log::info('[AuthController@login] Login attempt received.');
        Log::info('[AuthController@login] Request Path: ' . $request->path());
        Log::info('[AuthController@login] Request Body: ', $request->all());
        Log::info('[AuthController@login] X-XSRF-TOKEN Header: ' . $request->header('X-XSRF-TOKEN'));
        Log::info('[AuthController@login] Origin Header: ' . $request->header('Origin'));
        Log::info('[AuthController@login] Referer Header: ' . $request->header('Referer'));
        Log::info('[AuthController@login] Cookies Sent by Browser: ' . $request->header('Cookie')); // Log semua cookie yang dikirim browser

        if ($request->hasSession()) {
            Log::info('[AuthController@login] Session is available for request.');
            Log::info('[AuthController@login] Session ID: ' . $request->session()->getId());
            Log::info('[AuthController@login] Session CSRF Token: ' . $request->session()->token());
            Log::info('[AuthController@login] Session is started: ' . ($request->session()->isStarted() ? 'Yes' : 'No'));
            // Log::info('[AuthController@login] All Session Data: ', $request->session()->all()); // Bisa diaktifkan jika perlu detail sesi
        } else {
            Log::info('[AuthController@login] No session available for this request.');
        }
        Log::info('----------------------------------------------------');
        // Logging untuk debugging - AKHIR

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data login tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            // Buat token untuk user. Anda bisa memberi nama tokennya, misal 'admin-token'
            // Pastikan user model Anda menggunakan HasApiTokens trait
            $token = $user->createToken('admin-auth-token', ['role:admin'])->plainTextToken; // Tambahkan abilities jika perlu

            return response()->json([
                'message' => 'Login berhasil',
                'token' => $token,
                'user' => [ // Kirim data user yang relevan
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        }

        return response()->json(['message' => 'Kredensial tidak valid atau akun tidak ditemukan.'], 401);
    }

    public function logout(Request $request)
    {
        // Jika menggunakan token-based auth, hapus token saat ini
        if ($request->user()) { // Memastikan ada user yang terotentikasi dari token
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logout berhasil']);
        }
        
        // Jika menggunakan sesi (untuk SPA), maka logout sesi web
        // Auth::guard('web')->logout(); // Uncomment jika logout juga harus menghancurkan sesi web
        // $request->session()->invalidate(); // Uncomment jika logout juga harus menghancurkan sesi web
        // $request->session()->regenerateToken(); // Uncomment jika logout juga harus menghancurkan sesi web


        return response()->json(['message' => 'Tidak ada user yang terotentikasi atau sesi tidak aktif'], 200); // Bisa juga 401 jika user harus terotentikasi untuk logout
    }
}