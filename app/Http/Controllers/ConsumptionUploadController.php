<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Product;
use App\Models\EligibleItem;
use App\Imports\ConsumptionImport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ConsumptionUploadController extends Controller
{
    /**
     * Upload and process Excel file with consumption data
     */
    public function upload(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls',
            'facility_id' => 'required|exists:facilities,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get facility
            $facility = Facility::findOrFail($request->facility_id);
            
            // Load Excel file
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Extract month-year values from the Excel file to know which data to clear
            $monthYears = [];
            $headers = $worksheet->toArray()[0];
            foreach ($headers as $index => $header) {
                if ($index >= 2 && !str_contains($header, 'AMC')) { // Skip SN, Item Description, and AMC columns
                    // Convert month header (e.g., "Jan-25") to database format ("2025-01")
                    $monthParts = explode('-', $header);
                    if (count($monthParts) == 2) {
                        $month = $this->getMonthNumber($monthParts[0]);
                        $year = '20' . $monthParts[1]; // Convert "25" to "2025"
                        $monthYears[] = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                    }
                }
            }
            
            // Clear existing data for this facility and these month-years
            if (!empty($monthYears)) {
                Log::info("Clearing existing data for facility ID: {$facility->id} and months: " . implode(', ', $monthYears));
                DB::table('monthly_consumptions')
                    ->where('facility_id', $facility->id)
                    ->whereIn('month_year', $monthYears)
                    ->delete();
            }
            
            // Get data from Excel
            $data = [];
            $rows = $worksheet->toArray();
            
            // Skip header row
            array_shift($rows);
            
            // Validate headers
            $requiredColumns = ['SN', 'Item Description'];
            $monthColumns = [];
            
            foreach ($headers as $index => $header) {
                if ($index >= 2) { // Skip SN and Item Description columns
                    // Check if it's a month column (not AMC)
                    if (!str_contains($header, 'AMC')) {
                        $monthColumns[] = [
                            'index' => $index,
                            'name' => $header
                        ];
                    }
                }
            }
            
            if (count($monthColumns) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid month columns found in the Excel file'
                ], 422);
            }
            
            // Process data rows
            $records = [];
            $productCache = [];
            $recordCount = 0;
            
            foreach ($rows as $row) {
                // Skip empty rows
                if (empty($row[1])) {
                    continue;
                }
                
                $itemName = $row[1];
                
                // Find or create product
                if (!isset($productCache[$itemName])) {
                    $product = Product::firstOrCreate(
                        ['name' => $itemName],
                        ['description' => $itemName]
                    );
                    $productCache[$itemName] = $product->id;
                }
                
                $productId = $productCache[$itemName];
                
                // Process each month column
                foreach ($monthColumns as $monthCol) {
                    $monthIndex = $monthCol['index'];
                    $monthName = $monthCol['name'];
                    
                    // Parse month-year from header (e.g., "Jan-25" to "2025-01")
                    if (preg_match('/([A-Za-z]{3})-(\d{2})/', $monthName, $matches)) {
                        $month = $this->getMonthNumber($matches[1]);
                        $year = '20' . $matches[2]; // Convert "25" to "2025"
                        $monthYear = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                        
                        // Get quantity from cell
                        $quantity = is_numeric($row[$monthIndex]) ? (int)$row[$monthIndex] : 0;
                        
                        // Calculate AMC (for now, just use the quantity as AMC)
                        // In a real implementation, you would calculate the average based on multiple months
                        $amc = $quantity;
                        
                        // Add to records
                        $records[] = [
                            'facility_id' => $facility->id,
                            'product_id' => $productId,
                            'month_year' => $monthYear,
                            'quantity' => $quantity,
                            'amc' => $amc,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        $recordCount++;
                        
                        // Insert in batches to improve performance
                        if (count($records) >= 100) {
                            // Use insert instead of insertOrIgnore since we've already cleared existing data
                            DB::table('monthly_consumptions')->insert($records);
                            $records = [];
                        }
                    }
                }
            }
            
            // Insert any remaining records
            if (!empty($records)) {
                // Use insert instead of insertOrIgnore since we've already cleared existing data
                DB::table('monthly_consumptions')->insert($records);
            }
            
            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$recordCount} consumption records from the Excel file."
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing Excel file: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Convert month name to number
     */
    private function getMonthNumber($monthName)
    {
        $months = [
            'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6,
            'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
        ];
        
        return $months[$monthName] ?? 1;
    }
}
