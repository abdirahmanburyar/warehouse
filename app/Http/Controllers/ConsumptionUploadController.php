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
use App\Imports\MonthlyFacilityConsumptionImport;

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
                'month_year' => 'required|date_format:Y-m',
            ]);

            $file = $request->file('file');
            $facilityId = $request->input('facility_id');
            $monthYear = $request->input('month_year');
            
            try {
                // Queue the import process
                Excel::queueImport(new MonthlyFacilityConsumptionImport($facilityId, $monthYear), $file);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Monthly consumption data import has been queued and will be processed shortly'
                ]);
            } catch (\Exception $e) {
                Log::error('Monthly consumption import failed: ' . $e->getMessage());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Import failed: ' . $e->getMessage()
                ], 422);
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
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
