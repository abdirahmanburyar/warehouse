<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\IssuedQuantity;
use App\Models\AvarageMonthlyconsumption;
use App\Models\Facility;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;


class ReportController extends Controller
{
    public function index(Request $request){
        return inertia('Report/Index');
    } 
    public function stockLevelReport(Request $request){
        return inertia('Report/stockLevelReport');
    } 


    public function issuedQuantity(Request $request){
        $issuedQuantities = IssuedQuantity::get();
        return inertia('Report/IssuedQuantity', [
            'quantiteis' => $issuedQuantities
        ]);
    }  
    
    // mnthly consumption by facilities [AMC]

    public function monthlyConsumption(Request $request)
    {
        $facilityId = $request->input('facility_id');
        // Product filtering is now handled client-side
        $startMonth = $request->input('start_month', Carbon::now()->startOfYear()->format('Y-m'));
        $endMonth = $request->input('end_month', Carbon::now()->format('Y-m'));
        $isSubmitted = $request->input('is_submitted', false);
        
        // Initialize empty data
        $pivotData = [];
        $monthsQuery = collect([]);
        $facilityInfo = null;
        
        // Only fetch data if the form has been submitted with valid filters
        if ($isSubmitted && $facilityId && $startMonth && $endMonth) {
            // Facility information with manager (user)
            $facilityInfo = Facility::with('user')
                ->select('id', 'name', 'facility_type', 'email', 'phone', 'address', 'user_id')
                ->where('id', $facilityId)
                ->first();
            
            // Get all months in the range for our pivot table columns
            $monthsQuery = DB::table('monthly_consumptions')
                ->select('month_year')
                ->where('facility_id', $facilityId)
                ->where('month_year', '>=', $startMonth)
                ->where('month_year', '<=', $endMonth)
                ->distinct()
                ->orderBy('month_year')
                ->pluck('month_year');
            
            if (count($monthsQuery) > 0) {
                // Build the dynamic SQL for the pivot table
                $monthColumns = [];
                
                // Just add the regular month columns for now
                foreach ($monthsQuery as $month) {
                    $monthColumns[] = "MAX(CASE WHEN mc.month_year = '{$month}' THEN mc.quantity ELSE 0 END) as '{$month}'";
                }
                
                $monthColumnsStr = implode(", ", $monthColumns);
                
                // Build the main query with dynamic pivot
                $query = DB::table('monthly_consumptions as mc')
                    ->join('products as p', 'mc.product_id', '=', 'p.id')
                    ->select(
                        'mc.product_id',
                        'p.name as item_name'
                    )
                    ->selectRaw($monthColumnsStr)
                    ->where('mc.facility_id', $facilityId)
                    ->where('mc.month_year', '>=', $startMonth)
                    ->where('mc.month_year', '<=', $endMonth);
                
                // Product filtering is now handled client-side
                
                // Group by product
                $query->groupBy('mc.product_id', 'p.name');
                
                // Execute the query
                $pivotData = $query->get();
                
                // Convert to array and ensure all month columns exist
                $pivotData = json_decode(json_encode($pivotData), true);
                
                // Add serial numbers to the data
                $pivotData = array_map(function($item, $index) {
                    $item['sn'] = $index + 1;
                    return $item;
                }, $pivotData, array_keys($pivotData));
            }
        }
        
        return Inertia::render('Report/MonthlyConsumption', [
            'pivotData' => $pivotData,
            'months' => $monthsQuery,
            'facilities' => Facility::select('id', 'name', 'facility_type')->get(),
            'products' => Product::select('id', 'name')->get(),
            'facilityInfo' => $facilityInfo,
            'filters' => [
                'facility_id' => $facilityId,
                'start_month' => $startMonth,
                'end_month' => $endMonth
            ]
        ]);
    }
}
