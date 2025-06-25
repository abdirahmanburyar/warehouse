<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\IssueQuantitiyImport;
use Illuminate\Support\Facades\File;

class ProcessIssueQuantityImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $monthYear;
    protected $userId;

    public function __construct($filePath, $monthYear, $userId)
    {
        $this->filePath = $filePath;
        $this->monthYear = $monthYear;
        $this->userId = $userId;
    }

    public function handle()
    {
        \Log::info('Starting import', [
            'file' => $this->filePath,
            'exists' => file_exists($this->filePath)
        ]);

        if (!file_exists($this->filePath)) {
            \Log::error('File not found', ['path' => $this->filePath]);
            throw new \Exception("File not found at path: " . $this->filePath);
        }

        try {
            Excel::import(
                new IssueQuantitiyImport($this->monthYear, $this->userId),
                $this->filePath
            );
            
            // Delete the file after successful import
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
            
        } catch (\Exception $e) {
            \Log::error('Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('Import job failed', [
            'error' => $exception->getMessage(),
            'file' => $this->filePath
        ]);
        
        // You might want to notify the user here
    }
}