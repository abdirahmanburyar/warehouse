<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\IssuedQuantity;
use App\Models\AvarageMonthlyconsumption;
use App\Models\Facility;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ReceivedQuantity;
use App\Models\Warehouse;
use App\Http\Resources\ReceivedQuantityResource;
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

    public function physicalCountReport(Request $request){
        $query = Inventory::query()
            ->with(['product' => function($query) {
                $query->with(['dosage', 'category']);
            }])
            ->orderBy('expiry_date');

        // Apply filters
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('expiry_date')) {
            $query->whereDate('expiry_date', '<=', $request->expiry_date);
        }

        $inventories = $query->paginate($request->input('per_page', 100))
            ->withQueryString();
        
        return Inertia::render('Report/PhysicalCount', [
            'inventories' => $inventories,
            'products' => Product::orderBy('name')->get(),
            'categories' => DB::table('categories')->orderBy('name')->get(),
            'filters' => $request->only(['product_id', 'category_id', 'expiry_date', 'per_page']),
        ]);
    }
    
    /**
     * Save physical count data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function savePhysicalCount(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'physical_count_data' => 'required|array',
                'physical_count_data.*.inventory_id' => 'required|exists:inventories,id',
                'physical_count_data.*.physical_count' => 'required|numeric|min:0',
                'physical_count_data.*.difference' => 'required|numeric',
                'physical_count_data.*.remarks' => 'nullable|string',
            ]);
            
            // Begin transaction
            DB::beginTransaction();
            
            // Process each inventory item
            foreach ($request->physical_count_data as $data) {
                $inventory = Inventory::findOrFail($data['inventory_id']);
                
                // Update or create physical count record
                // Note: You may need to create a PhysicalCount model if it doesn't exist
                $inventory->update([
                    'physical_count' => $data['physical_count'],
                    'physical_count_difference' => $data['difference'],
                    'physical_count_remarks' => $data['remarks'],
                    'physical_count_date' => now(),
                ]);
            }
            
            // Commit transaction
            DB::commit();
            
            return response()->json([
                'message' => 'Physical count data saved successfully',
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to save physical count data: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }


    public function issuedQuantity(Request $request){
        $issuedQuantities = IssuedQuantity::get();
        return inertia('Report/IssuedQuantity', [
            'quantiteis' => $issuedQuantities
        ]);
    }  
    
    // mnthly consumption by facilities [AMC]

    public function receivedQuantities(Request $request)
    {
        $query = ReceivedQuantity::query()
            ->with(['product.dosage','product.category', 'receiver', 'transfer', 'packingList']);

        // Apply filters
        // Warehouse filter removed as warehouse_id doesn't exist in the product table

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('received_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('received_at', '<=', $request->end_date);
        }

        if ($request->filled('source')) {
            if ($request->source === 'transfer') {
                $query->whereNotNull('transfer_id')->whereNull('packing_list_id');
            } elseif ($request->source === 'packing_list') {
                $query->whereNotNull('packing_list_id')->whereNull('transfer_id');
            }
        }
        
        $receivedQuantities = $query->paginate($request->input('per_page', 1), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

            $receivedQuantities->setPath(url()->current()); // Force Laravel to use full URLs
        

        return Inertia::render('Report/ReceivedQuantities', [
            'receivedQuantities' => ReceivedQuantityResource::collection($receivedQuantities),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'filters' => $request->only(['warehouse_id', 'product_id', 'start_date', 'end_date', 'source', 'per_page']),
        ]);
    }

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
                
                // Add the regular month columns
                foreach ($monthsQuery as $month) {
                    $monthColumns[] = "MAX(CASE WHEN mc.month_year = '{$month}' THEN mc.quantity ELSE 0 END) as '{$month}'";
                }
                
                // Add AMC column (calculated from the last 3-4 months)
                $monthsArray = $monthsQuery->toArray();
                $monthCount = count($monthsArray);
                
                if ($monthCount >= 3) {
                    // Take the last 3 or 4 months for AMC calculation
                    $amcMonths = array_slice($monthsArray, max(0, $monthCount - 4), min(4, $monthCount));
                    $amcCalc = [];
                    
                    foreach ($amcMonths as $month) {
                        $amcCalc[] = "COALESCE(MAX(CASE WHEN mc.month_year = '{$month}' THEN mc.quantity ELSE 0 END), 0)";
                    }
                    
                    $amcFormula = "ROUND((".implode(" + ", $amcCalc).") / ".count($amcCalc).", 0)";
                    $monthColumns[] = "{$amcFormula} as 'AMC'";  
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
