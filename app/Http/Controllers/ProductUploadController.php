<?php

namespace App\Http\Controllers;

use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProductUploadController extends Controller
{
    /**
     * Upload and process Excel file with product data
     */
    public function upload(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Increase PHP execution time limit for large imports
            set_time_limit(300); // 5 minutes
            
            // Start database transaction
            DB::beginTransaction();
            
            // Create a new instance of ProductsImport
            $import = new ProductsImport();
            
            // Import the Excel file
            Excel::import($import, $request->file('file'));
            
            // Commit the transaction if everything is successful
            DB::commit();
            
            // Get import statistics
            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();
            $errors = $import->getErrors();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedCount} products. Skipped {$skippedCount} products.",
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            
            Log::error('Excel import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error importing products: ' . $e->getMessage()
            ], 500);
        }
    }
}
