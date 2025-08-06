<?php

namespace App\Http\Controllers;

use App\Imports\ProcessMonthlyConsumptionImport;
use App\Models\Facility;
use App\Models\Product;
use App\Models\EligibleItem;
use App\Models\MonthlyConsumptionReport;
use App\Models\MonthlyConsumptionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Cache;

class ConsumptionUploadController extends Controller
{
    /**
     * Upload and process Excel file with consumption data
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
                'facility_id' => 'required|exists:facilities,id',
            ]);

            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file was uploaded'
                ], 422);
            }

            $file = $request->file('file');
            
            // Validate file
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file'
                ], 422);
            }

            // Create directory if it doesn't exist
            $uploadPath = public_path('excel-imports');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Generate unique filename and import ID
            $importId = uniqid();
            $filename = $importId . '.' . $file->getClientOriginalExtension();
            
            // Move file to public directory
            $file->move($uploadPath, $filename);
            $filePath = $uploadPath . '/' . $filename;

            Log::info('File uploaded successfully', [
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $filename,
                'path' => $filePath
            ]);

            // Initialize cache values
            Cache::put("import_{$importId}_imported", 0, now()->addHours(1));
            Cache::put("import_{$importId}_skipped", 0, now()->addHours(1));
            Cache::put("import_{$importId}_errors", [], now()->addHours(1));

            // Create and run the import
            $import = new ProcessMonthlyConsumptionImport(
                $filePath,
                $request->facility_id,
                $importId
            );

            $result = $import->import();

            // Store results in cache
            Cache::put("import_{$importId}_imported", $result['imported'], now()->addHours(1));
            Cache::put("import_{$importId}_skipped", $result['skipped'], now()->addHours(1));
            Cache::put("import_{$importId}_errors", $result['errors'], now()->addHours(1));

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check the status of an import
     */
    public function checkImportStatus($importId)
    {
        $imported = Cache::get("import_{$importId}_imported", 0);
        $skipped = Cache::get("import_{$importId}_skipped", 0);
        $errors = Cache::get("import_{$importId}_errors", []);

        return response()->json([
            'success' => true,
            'data' => [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors
            ]
        ]);
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
