<?php

namespace App\Http\Controllers\Api; // Sesuaikan namespace jika berbeda

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator; // Tambahkan ini
use App\Models\User; // Pastikan model User di-import

class AuthController extends Controller
{
    public function login(Request $request)
    {
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
        return response()->json(['message' => 'Tidak ada user yang terotentikasi'], 401);
    }
}
