<?php

namespace App\Console\Commands;

use App\Jobs\GenerateMonthlyInventoryReportJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateInventoryMonthlyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:generate-monthly-report {month? : The month in YYYY-MM format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly inventory reports using InventoryReport and InventoryReportItem models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $monthYear = $this->argument('month') ?? Carbon::now()->format('Y-m');
        
        $this->info("Dispatching monthly inventory report generation job for {$monthYear}");
        
        try {
            // Dispatch the job to the queue
            GenerateMonthlyInventoryReportJob::dispatch($monthYear);
            
            $this->info("Monthly inventory report job dispatched successfully for {$monthYear}");
            $this->info("The job will run in the background and an email notification will be sent to buryar313@gmail.com when completed.");
            
            Log::info("Monthly inventory report job dispatched for {$monthYear}");
            
            return 0;
            
        } catch (\Exception $e) {
            Log::error('Error dispatching inventory monthly report job: ' . $e->getMessage());
            $this->error("Error dispatching job: {$e->getMessage()}");
            return 1;
        }
    }
}
