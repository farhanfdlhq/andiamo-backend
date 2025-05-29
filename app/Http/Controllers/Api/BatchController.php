<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
// Hapus use Illuminate\Support\Arr; // Tidak perlu lagi jika $fillable sudah benar

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

        // Sorting
        $sortBy = $request->input('sortBy', 'departure_date'); // Default sort
        $sortDir = $request->input('sortDir', 'desc'); // Default direction
        $allowedSortColumns = ['name', 'region', 'status', 'departure_date', 'arrival_date'];

        if (in_array($sortBy, $allowedSortColumns) && in_array(strtolower($sortDir), ['asc', 'desc'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('departure_date', 'desc'); // Fallback default sort
        }

        $batches = $query->get();
        // ... (pastikan image_urls di-decode ke array jika disimpan sebagai JSON dan tidak ada $casts)
        return response()->json($batches);
    }

    public function show(Batch $batch)
    {
        // Dengan $casts['image_urls' => 'array'] di model, $batch->image_urls sudah otomatis jadi array
        // atau null jika di DB null. Pastikan frontend bisa handle null.
        // Jika ingin selalu array kosong jika null:
        $batch->image_urls = $batch->image_urls ?? [];
        return response()->json($batch);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'shortDescription' => 'nullable|string|max:500',
            'region' => 'nullable|string|max:100',
            'departure_date' => 'nullable|date',
            'arrival_date' => 'nullable|date|after_or_equal:departure_date',
            'whatsappLink' => 'nullable|url|max:255', // Pastikan frontend mengirim URL lengkap
            'status' => 'required|string|in:active,closed', // 'required' lebih baik dari 'nullable' untuk status default
            'images' => 'nullable|array|max:50',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 'nullable' di sini tidak perlu jika 'images' sudah nullable
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data yang dikirim tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $uploadedImagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                if ($imageFile && $imageFile->isValid()) { // Tambahan check isValid()
                    $path = $imageFile->store('batches', 'public');
                    $uploadedImagePaths[] = $path;
                }
            }
        }
        // Dengan $casts di model, Eloquent akan handle konversi array ke JSON saat menyimpan
        $validatedData['image_urls'] = $uploadedImagePaths; // Ini sudah array
        unset($validatedData['images']); // Hapus 'images' karena sudah di-handle

        // Pastikan semua field di $validatedData (termasuk image_urls) ada di $fillable Model Batch
        $batch = Batch::create($validatedData);

        // $batch->image_urls sudah otomatis array karena $casts di Model
        // Jika ingin memastikan selalu array kosong jika null (seharusnya sudah di-handle $casts)
        $batch->image_urls = $batch->image_urls ?? [];

        return response()->json($batch, 201);
    }

    public function update(Request $request, Batch $batch)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
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

        // Dapatkan path gambar yang sudah ada (sudah jadi array karena $casts di model)
        $currentImagePaths = $batch->image_urls ?? [];

        if ($request->hasFile('images') && !empty($request->file('images'))) {
            // Hapus gambar lama jika 'replace_existing_images' true atau jika ada file baru diupload
            // (Anda bisa sesuaikan logika ini. Misal, hanya hapus jika replace_existing_images true)
            if ($request->boolean('replace_existing_images') || $request->hasFile('images')) {
                foreach ($currentImagePaths as $oldPath) {
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $currentImagePaths = []; // Mulai dengan array kosong jika gambar lama dihapus
            }

            $newUploadedPaths = [];
            foreach ($request->file('images') as $imageFile) {
                if ($imageFile && $imageFile->isValid()) {
                    $path = $imageFile->store('batches', 'public');
                    $newUploadedPaths[] = $path;
                }
            }
            // Gabungkan path lama (jika tidak dihapus) dengan yang baru, pastikan unik
            $validatedData['image_urls'] = array_values(array_unique(array_merge($currentImagePaths, $newUploadedPaths)));
        } else {
            // Jika tidak ada file gambar baru diupload, pertahankan image_urls yang sudah ada (sudah berupa array)
            $validatedData['image_urls'] = $currentImagePaths;
        }
        unset($validatedData['images']);
        unset($validatedData['replace_existing_images']);

        $batch->update($validatedData);

        // $batch->image_urls sudah otomatis array
        $batch->image_urls = $batch->image_urls ?? [];

        return response()->json($batch);
    }

    public function destroy(Batch $batch)
    {
        // $batch->image_urls sudah array karena $casts
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
