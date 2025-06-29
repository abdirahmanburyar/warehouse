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

class ProcessProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $importId;

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
            $import = new ProductsImport($this->filePath);
            $result = $import->import();

            // Store results in cache
            Cache::put("import_{$this->importId}_imported", $result['imported'], now()->addHours(1));
            Cache::put("import_{$this->importId}_skipped", $result['skipped'], now()->addHours(1));
            Cache::put("import_{$this->importId}_errors", $result['errors'], now()->addHours(1));

            // Clean up the file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

            Log::info('Product import completed', [
                'import_id' => $this->importId,
                'results' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Product import failed', [
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Store error in cache
            Cache::put("import_{$this->importId}_errors", [$e->getMessage()], now()->addHours(1));
            
            // Clean up the file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

            throw $e;
        }
    }
} 