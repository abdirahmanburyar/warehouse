<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarehouseMonthlyReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $reportData;
    protected $monthYear;

    public function __construct($reportData, $monthYear)
    {
        $this->reportData = $reportData;
        $this->monthYear = $monthYear;
    }

    public function collection()
    {
        return collect($this->reportData);
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'UoM',
            'Batch Number',
            'Expiry Date',
            'Beginning Balance',
            'Received Quantity',
            'Issued Quantity',
            'Other Quantity Out',
            'Positive Adjustment',
            'Negative Adjustment',
            'Closing Balance',
            'Total Closing Balance',
            'Average Monthly Consumption',
            'Months of Stock',
            'Quantity in Pipeline',
            'Unit Cost',
            'Total Cost',
        ];
    }

    public function map($item): array
    {
        return [
            $item->product->name ?? 'N/A',
            $item->uom ?? 'unit',
            $item->batch_number ?? 'N/A',
            $item->expiry_date ? $item->expiry_date->format('Y-m-d') : 'N/A',
            $item->beginning_balance ?? 0,
            $item->received_quantity ?? 0,
            $item->issued_quantity ?? 0,
            $item->other_quantity_out ?? 0,
            $item->positive_adjustment ?? 0,
            $item->negative_adjustment ?? 0,
            $item->closing_balance ?? 0,
            $item->total_closing_balance ?? 0,
            round($item->average_monthly_consumption ?? 0, 2),
            $item->months_of_stock ?? 0,
            $item->quantity_in_pipeline ?? 0,
            round($item->unit_cost ?? 0, 2),
            round($item->total_cost ?? 0, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as header
            1 => ['font' => ['bold' => true]],
            
            // Set auto width for all columns
            'A:M' => ['alignment' => ['horizontal' => 'center']],
        ];
    }

    public function title(): string
    {
        return 'Warehouse Monthly Report - ' . $this->monthYear;
    }
} 