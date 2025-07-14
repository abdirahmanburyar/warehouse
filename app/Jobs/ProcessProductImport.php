<?php

namespace App\Jobs;

use App\Imports\ProductsImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $importId;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $importId)
    {
        $this->filePath = $filePath;
        $this->importId = $importId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Verify file exists before processing
            if (!file_exists($this->filePath)) {
                throw new \Exception("Import file not found: {$this->filePath}");
            }

            // Ensure ProductsImport class is available
            if (!class_exists(ProductsImport::class)) {
                throw new \Exception("ProductsImport class not found. Please check autoloading.");
            }

            // Initialize cache with processing status
            Cache::put("import_{$this->importId}_status", 'processing', now()->addHours(1));

            $import = new ProductsImport($this->filePath);
            $result = $import->import();

            // Store results in cache
            Cache::put("import_{$this->importId}_imported", $result['imported'], now()->addHours(1));
            Cache::put("import_{$this->importId}_skipped", $result['skipped'], now()->addHours(1));
            Cache::put("import_{$this->importId}_errors", $result['errors'], now()->addHours(1));
            Cache::put("import_{$this->importId}_status", 'completed', now()->addHours(1));

            // Clean up the file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

            Log::info('Product import completed successfully', [
                'import_id' => $this->importId,
                'results' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Product import failed', [
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_path' => $this->filePath
            ]);

            // Store error in cache
            Cache::put("import_{$this->importId}_errors", [$e->getMessage()], now()->addHours(1));
            Cache::put("import_{$this->importId}_status", 'failed', now()->addHours(1));
            
            // Clean up the file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Product import job failed permanently', [
            'import_id' => $this->importId,
            'error' => $exception->getMessage(),
            'file_path' => $this->filePath
        ]);

        // Mark as failed in cache
        Cache::put("import_{$this->importId}_status", 'failed', now()->addHours(1));
        Cache::put("import_{$this->importId}_errors", [$exception->getMessage()], now()->addHours(1));
        
        // Clean up the file
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }
} 