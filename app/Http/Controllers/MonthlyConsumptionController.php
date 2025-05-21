<?php

namespace App\Http\Controllers;

use App\Models\AvarageMonthlyconsumption;
use App\Models\Facility;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonthlyConsumptionController extends Controller
{
    public function monthlyConsumption(Request $request)
    {
        // Get filter parameters
        $facilityId = $request->input('facility_id');
        $productId = $request->input('product_id');
        $startMonth = $request->input('start_month', date('Y-m', strtotime('-5 months')));
        $endMonth = $request->input('end_month', date('Y-m'));
        
        // Get all months in the range for our pivot table columns
        $monthsQuery = DB::table('monthly_consumptions')
            ->select('month_year')
            ->where('month_year', '>=', $startMonth)
            ->where('month_year', '<=', $endMonth)
            ->distinct()
            ->orderBy('month_year')
            ->pluck('month_year');
        
        // Build the dynamic SQL for the pivot table
        $monthColumns = [];
        foreach ($monthsQuery as $month) {
            $monthColumns[] = "MAX(CASE WHEN mc.month_year = '{$month}' THEN mc.quantity ELSE 0 END) as '{$month}'";
        }
        
        $monthColumnsStr = implode(", ", $monthColumns);
        
        // Build the main query with dynamic pivot
        $query = DB::table('monthly_consumptions as mc')
            ->join('facilities as f', 'mc.facility_id', '=', 'f.id')
            ->select(
                'mc.facility_id',
                'f.name as facility',
                'f.facility_type'
            )
            ->selectRaw($monthColumnsStr)
            ->where('mc.month_year', '>=', $startMonth)
            ->where('mc.month_year', '<=', $endMonth)
            ->groupBy('mc.facility_id', 'f.name', 'f.facility_type');
        
        // Apply filters if provided
        if ($facilityId) {
            $query->where('mc.facility_id', $facilityId);
        }
        
        if ($productId) {
            $query->where('mc.product_id', $productId);
        }
        
        // Execute the query
        $pivotData = $query->get();
        
        // Convert to array and ensure all month columns exist (even if empty)
        $pivotData = json_decode(json_encode($pivotData), true);
        
        return Inertia::render('Report/MonthlyConsumption', [
            'pivotData' => $pivotData,
            'months' => $monthsQuery,
            'facilities' => Facility::select('id', 'name', 'facility_type')->get(),
            'products' => Product::select('id', 'name')->get(),
            'filters' => [
                'facility_id' => $facilityId,
                'product_id' => $productId,
                'start_month' => $startMonth,
                'end_month' => $endMonth
            ]
        ]);
    }
}
