<?php

namespace App\Http\Controllers\Api\Admin; // Pastikan namespace ini benar

use App\Http\Controllers\Controller;
use App\Models\Setting; // Pastikan use App\Models\Setting; ada dan benar
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        try { // Tambahkan try-catch
            $settings = Setting::all()->pluck('value', 'key');
            return response()->json($settings);
        } catch (\Exception $e) {
            Log::error('Error in SettingController@index: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil pengaturan.', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try { // Tambahkan try-catch
            $validator = Validator::make($request->all(), [
                'defaultWhatsAppNumber' => 'nullable|string|max:20',
                'defaultCTAMessage' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
            }

            $settingsToUpdate = $validator->validated();

            foreach ($settingsToUpdate as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value ?? '']
                );
            }

            return response()->json(['message' => 'Pengaturan berhasil disimpan.']);
        } catch (\Exception $e) {
            Log::error('Error in SettingController@store: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan pengaturan.', 'error' => $e->getMessage()], 500);
        }
    }
}
