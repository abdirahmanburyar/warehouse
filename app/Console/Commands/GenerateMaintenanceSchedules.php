<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asset;
use Carbon\Carbon;

class GenerateMaintenanceSchedules extends Command
{
    protected $signature = 'assets:generate-maintenance-schedules';
    protected $description = 'Generate next maintenance due dates for assets based on maintenance_interval_months';

    public function handle(): int
    {
        $assets = Asset::whereNotNull('maintenance_interval_months')->get();
        $updated = 0;
        foreach ($assets as $asset) {
            $last = $asset->last_maintenance_at ? Carbon::parse($asset->last_maintenance_at) : ($asset->purchase_date ?? $asset->acquisition_date);
            if (!$last) continue;
            $next = Carbon::parse($last)->addMonths(max(1, (int) $asset->maintenance_interval_months));
            $asset->metadata = array_merge((array) $asset->metadata, [ 'maintenance_due_date' => $next->toDateString() ]);
            $asset->save();
            $updated++;
        }
        $this->info("Updated maintenance schedule for {$updated} assets");
        return 0;
    }
}

