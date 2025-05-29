<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password; // Untuk aturan password yang lebih kuat

class ProfileController extends Controller
{
    public function changePassword(Request $request)
    {
        $user = Auth::user(); // Mendapatkan user yang sedang login

        if (!$user) {
            return response()->json(['message' => 'Tidak terotentikasi.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Password saat ini tidak cocok.');
                }
            }],
            'new_password' => ['required', 'string', Password::min(8)->numbers(), 'confirmed'],
            // 'new_password_confirmation' akan otomatis divalidasi oleh 'confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
        }

        // Update password user
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Opsional: Batalkan semua token lain milik user ini agar mereka login ulang di perangkat lain
        // $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json(['message' => 'Password berhasil diubah.']);
    }

    // Contoh jika Anda ingin update profil (nama, email)

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Tidak terotentikasi.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return response()->json(['message' => 'Profil berhasil diperbarui.', 'user' => $user]);
    }
}
