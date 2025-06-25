<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\IssueQuantitiyImport;

class ProcessIssueQuantityImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;
    protected $monthYear;
    protected $userId;

    public function __construct($file, $monthYear, $userId)
    {
        $this->file = $file;
        $this->monthYear = $monthYear;
        $this->userId = $userId;
    }

    public function handle()
    {
        Excel::import(
            new IssueQuantitiyImport($this->monthYear, $this->userId),
            $this->file
        );
    }
}