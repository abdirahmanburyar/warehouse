<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Facility;
use App\Models\EligibleItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\InventoryAllocation;
use App\Jobs\ProcessFacilityQuarterlyOrder;

class GenerateQuarterlyOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:generate-quarterly {quarter? : Target quarter (1-4)} {year? : Target year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate quarterly orders for all active facilities. Optionally specify quarter (1-4) and year.';

    private const QUARTER_START_DATES = [
        1 => '01-01',
        2 => '01-04',
        3 => '01-07',
        4 => '01-10',
    ];

    public function handle()
    {
        $this->info('Starting quarterly order generation...');

        $today = now();
        
        // If quarter and year are not provided, calculate the next quarter
        if (!$this->argument('quarter') && !$this->argument('year')) {
            $currentQuarter = ceil($today->month / 3);
            $targetQuarter = $currentQuarter % 4 + 1; // Next quarter (1-4)
            $year = $today->year;
            
            // If we're in Q4, increment the year
            if ($currentQuarter === 4) {
                $year++;
            }
        } else {
            // Use provided quarter and year if specified
            $targetQuarter = $this->argument('quarter') ?? ceil($today->month / 3);
            $year = $this->argument('year') ?? $today->year;
        }

        $this->info("Generating orders for Q{$targetQuarter} {$year}");

        // Calculate order start date (first day of the quarter)
        $orderStartDate = Carbon::create($year, ($targetQuarter - 1) * 3 + 1, 1);

        // Get all active facilities
        $facilities = Facility::where('is_active', true)->get();
        $totalFacilities = $facilities->count();

        $this->info("Found {$totalFacilities} active facilities");
        $bar = $this->output->createProgressBar($totalFacilities);
        $bar->start();

        // Process each facility in a separate job
        foreach ($facilities as $facility) {
            ProcessFacilityQuarterlyOrder::dispatch($facility, $targetQuarter, $year, $orderStartDate, $today)
                ->onQueue('quarterly-orders');
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All quarterly order generation jobs have been queued.');
        $this->info('Please monitor the queue worker logs for progress.');
    }
}
