<?php

namespace App\Http\Controllers\Api\Admin; // Pastikan namespace ini benar

use App\Http\Controllers\Controller;
use App\Models\Batch; // Pastikan use App\Models\Batch; ada dan benar
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// use Illuminate\Support\Facades\DB; // Tidak terpakai saat ini, bisa dihapus jika tidak ada query DB kompleks

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        try { // Tambahkan try-catch untuk debugging
            $totalBatches = Batch::count();
            $activeBatches = Batch::where('status', 'active')->count();
            $closedBatches = Batch::where('status', 'closed')->count();

            return response()->json([
                'totalBatches' => $totalBatches,
                'activeBatches' => $activeBatches,
                'closedBatches' => $closedBatches,
            ]);
        } catch (\Exception $e) {
            // Log errornya untuk detail lebih lanjut
            Log::error('Error in DashboardController@summary: ' . $e->getMessage());
            // Kembalikan respons error yang lebih informatif
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil summary dashboard.', 'error' => $e->getMessage()], 500);
        }
    }
}
