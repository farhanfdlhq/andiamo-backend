<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // Import Schedule Facade
use App\Models\Batch; // Import model Batch

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan perintah untuk update status batch
Schedule::call(function () {
    $today = now()->toDateString();
    $updatedCount = Batch::where('status', 'active')
        ->whereNotNull('arrival_date')
        ->where('arrival_date', '<', $today)
        ->update(['status' => 'closed']);
    // Log atau output jika perlu
    Log::info($updatedCount . ' batches have been automatically closed by scheduler.');
})->daily()->name('updateBatchStatus')->withoutOverlapping(); // Tambahkan name dan withoutOverlapping