<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Pastikan ini ada

class BatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Batch::query();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('region') && $request->region !== 'all') {
            $query->where('region', $request->region);
        }

        $sortBy = $request->input('sortBy', 'departure_date');
        $sortDir = $request->input('sortDir', 'desc');
        $allowedSortColumns = ['name', 'region', 'status', 'departure_date', 'arrival_date'];

        if (in_array($sortBy, $allowedSortColumns) && in_array(strtolower($sortDir), ['asc', 'desc'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('departure_date', 'desc');
        }

        $batches = $query->get();
        return response()->json($batches);
    }

    public function show(Batch $batch)
    {
        $batch->image_urls = $batch->image_urls ?? [];
        return response()->json($batch);
    }

    public function store(Request $request)
    {
        // Coba log ke stderr (mungkin muncul di log error Nginx/PHP-FPM) dan juga ke file log default
        Log::channel('stderr')->info('BatchController@store: Request received.');
        Log::info('BatchController@store: Request received.', ['all_request_data' => $request->all()]);

        // Bungkus semuanya dalam try-catch yang lebih umum untuk menangkap Throwable
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'shortDescription' => 'nullable|string|max:500',
                'region' => 'nullable|string|max:100',
                'departure_date' => 'nullable|date',
                'arrival_date' => 'nullable|date|after_or_equal:departure_date',
                'whatsappLink' => 'nullable|url|max:255',
                'status' => 'required|string|in:active,closed',
                'images' => 'nullable|array|max:50',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            if ($validator->fails()) {
                Log::channel('stderr')->error('BatchController@store: Validation failed.');
                Log::error('BatchController@store: Validation failed.', $validator->errors()->toArray());
                return response()->json(['message' => 'Data yang dikirim tidak valid.', 'errors' => $validator->errors()], 422);
            }

            Log::channel('stderr')->info('BatchController@store: Validation passed.');
            Log::info('BatchController@store: Validation passed.');

            $validatedData = $validator->validated();
            Log::info('BatchController@store: Validated data.', $validatedData);

            $uploadedImagePaths = [];

            if ($request->hasFile('images')) {
                Log::info('BatchController@store: Processing images.');
                foreach ($request->file('images') as $key => $imageFile) {
                    if ($imageFile && $imageFile->isValid()) {
                        Log::info('BatchController@store: Image file is valid.', ['key' => $key, 'original_name' => $imageFile->getClientOriginalName()]);
                        $path = $imageFile->store('batches', 'public'); // Biarkan ini melempar exception jika gagal
                        $uploadedImagePaths[] = $path;
                        Log::info('BatchController@store: Image stored.', ['path' => $path]);
                    } else {
                        Log::warning('BatchController@store: Invalid image file received.', ['key' => $key]);
                    }
                }
            } else {
                Log::info('BatchController@store: No images to process.');
            }
            
            $validatedData['image_urls'] = $uploadedImagePaths;
            unset($validatedData['images']);
            Log::info('BatchController@store: Image paths prepared for database.', ['image_urls' => $validatedData['image_urls']]);

            Log::info('BatchController@store: Attempting to create batch in database.');
            $batch = Batch::create($validatedData); // Biarkan ini melempar exception jika gagal
            Log::info('BatchController@store: Batch created successfully.', ['batch_id' => $batch->id]);

            $batch->image_urls = $batch->image_urls ?? [];
            return response()->json($batch, 201);

        } catch (\Throwable $e) { // Tangkap semua jenis error/exception
            Log::channel('stderr')->error('BatchController@store: CRITICAL ERROR - ' . $e->getMessage());
            Log::error('BatchController@store: CRITICAL ERROR.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data_at_error' => $request->all() // Log data request saat error
            ]);
            // Kembalikan respons error 500 yang lebih umum
            return response()->json(['message' => 'Terjadi kesalahan internal pada server.', 'error_detail' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Batch $batch)
    {
        // Anda bisa menambahkan logging serupa di sini jika diperlukan
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string', // Tambahkan validasi lain yang diperlukan dari store jika bisa diupdate
            'shortDescription' => 'nullable|string|max:500',
            'region' => 'nullable|string|max:100',
            'departure_date' => 'nullable|date',
            'arrival_date' => 'nullable|date|after_or_equal:departure_date',
            'whatsappLink' => 'nullable|url|max:255',
            'status' => 'sometimes|required|string|in:active,closed',
            'images' => 'nullable|array|max:50',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'replace_existing_images' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data yang dikirim tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $currentImagePaths = $batch->image_urls ?? [];

        if ($request->hasFile('images') && !empty($request->file('images'))) {
            if ($request->boolean('replace_existing_images') || $request->hasFile('images')) { // Kondisi ini bisa disesuaikan. Jika replace_existing_images true, ATAU jika memang ada file gambar baru (mungkin ingin selalu mengganti jika ada file baru, atau hanya jika flag true)
                foreach ($currentImagePaths as $oldPath) {
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $currentImagePaths = []; // Reset jika gambar lama dihapus
            }
            $newUploadedPaths = [];
            foreach ($request->file('images') as $imageFile) {
                if ($imageFile && $imageFile->isValid()) {
                    $path = $imageFile->store('batches', 'public');
                    $newUploadedPaths[] = $path;
                }
            }
            // Gabungkan path yang tersisa (jika ada) dengan yang baru, pastikan unik
            $validatedData['image_urls'] = array_values(array_unique(array_merge($currentImagePaths, $newUploadedPaths)));
        } else {
             // Jika tidak ada file baru dan tidak ada perintah hapus, pertahankan yang lama
            $validatedData['image_urls'] = $currentImagePaths;
        }
        unset($validatedData['images']);
        unset($validatedData['replace_existing_images']);

        $batch->update($validatedData);
        $batch->image_urls = $batch->image_urls ?? []; // Pastikan selalu array untuk response
        return response()->json($batch);
    }

    public function destroy(Batch $batch)
    {
        $imagePaths = $batch->image_urls ?? [];
        foreach ($imagePaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        $batch->delete();
        return response()->json(null, 204);
    }
}