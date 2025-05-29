<?php

namespace App\Console\Commands;

use App\Models\Batch;
use Illuminate\Console\Command;

class UpdateBatchStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:update-status';
    protected $description = 'Automatically closes batches whose arrival date has passed';

    public function handle()
    {
        $today = now()->toDateString();
        $updatedCount = Batch::where('status', 'active')
            ->whereNotNull('arrival_date')
            ->where('arrival_date', '<', $today)
            ->update(['status' => 'closed']);
        $this->info($updatedCount . ' batches have been automatically closed.');
        return 0;
    }
}
