<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class WarehouseAmcExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $pivotData;
    protected $monthYears;

    public function __construct($pivotData, $monthYears)
    {
        $this->pivotData = $pivotData;
        $this->monthYears = $monthYears;
    }

    public function array(): array
    {
        $exportData = [];
        
        foreach ($this->pivotData as $row) {
            $exportRow = [
                $row['name'],
                $row['category'],
                $row['dosage'],
            ];
            
            // Add consumption data for each month
            foreach ($this->monthYears as $monthYear) {
                $exportRow[] = $row[$monthYear] ?? 0;
            }
            
            // Add AMC column
            $exportRow[] = $row['AMC'] ?? 0;
            
            $exportData[] = $exportRow;
        }
        
        return $exportData;
    }

    public function headings(): array
    {
        $headers = ['Item', 'Category', 'Dosage Form'];
        
        // Add month headers
        foreach ($this->monthYears as $monthYear) {
            $headers[] = $this->formatMonthYear($monthYear);
        }
        
        // Add AMC header
        $headers[] = 'AMC';
        
        return $headers;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 40, // Item
            'B' => 20, // Category
            'C' => 20, // Dosage Form
        ];
        
        // Set width for month columns
        $currentColumn = 'D';
        foreach ($this->monthYears as $monthYear) {
            $widths[$currentColumn] = 15;
            $currentColumn++;
        }
        
        // Set width for AMC column
        $amcColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->monthYears) + 3);
        $widths[$amcColumn] = 20; // AMC column
        
        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $this->getLastColumn();
        $lastRow = count($this->pivotData) + 1; // +1 for header row
        
        // Style the header row
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '6B7280'], // Gray-500
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'], // Gray-300
                ],
            ],
        ]);
        
        // Style the data rows
        if ($lastRow > 1) {
            $sheet->getStyle("A2:{$lastColumn}{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'], // Gray-200
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            
            // Center align month data columns
            $monthStartColumn = 'D';
            $monthEndColumn = $this->getLastColumn();
            $amcColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->monthYears) + 3);
            
            // Center align month columns (excluding AMC column)
            $monthEndColumnBeforeAmc = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->monthYears) + 2);
            $sheet->getStyle("{$monthStartColumn}2:{$monthEndColumnBeforeAmc}{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            
            // Left align first three columns
            $sheet->getStyle("A2:C{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ]);
            
            // Style AMC column with special formatting
            $sheet->getStyle("{$amcColumn}2:{$amcColumn}{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '1E40AF'], // Blue-800
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DBEAFE'], // Blue-100
                ],
            ]);
        }
        
        // Add alternating row colors
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'], // Gray-50
                    ],
                ]);
            }
        }
        
        // Freeze the first row and first three columns
        $sheet->freezePane('D2');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastColumn = $this->getLastColumn();
                $lastRow = count($this->pivotData) + 1;
                
                // Auto-size columns based on content
                foreach (range('A', $lastColumn) as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }
                
                // Set row height for header
                $event->sheet->getRowDimension(1)->setRowHeight(30);
                
                // Set row height for data rows
                for ($row = 2; $row <= $lastRow; $row++) {
                    $event->sheet->getRowDimension($row)->setRowHeight(25);
                }
            },
        ];
    }

    private function getLastColumn()
    {
        $baseColumns = 3; // Item, Category, Dosage Form
        $monthColumns = count($this->monthYears);
        $amcColumn = 1; // AMC column
        $totalColumns = $baseColumns + $monthColumns + $amcColumn;
        
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColumns);
    }

    private function formatMonthYear($monthYear)
    {
        if (!$monthYear) return 'N/A';
        
        $parts = explode('-', $monthYear);
        if (count($parts) !== 2) return $monthYear;
        
        $year = $parts[0];
        $month = $parts[1];
        
        $date = \DateTime::createFromFormat('Y-m', $monthYear);
        if (!$date) return $monthYear;
        
        return $date->format('M Y'); // e.g., "Feb 2025"
    }
}
