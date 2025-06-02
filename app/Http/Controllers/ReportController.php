<?php

namespace App\Http\Controllers;

use App\Mail\PhysicalCountSubmitted;
use App\Models\IssuedQuantity;
use App\Models\AvarageMonthlyconsumption;
use App\Models\Location;
use App\Models\Product;
use App\Models\MonthlyQuantityReceived;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Order;
use App\Models\Facility;
use App\Models\Inventory;
use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use App\Models\IssueQuantityReport;
use App\Http\Resources\PhysicalCountReportResource;
use App\Models\Disposal;
use App\Models\Liquidation;
use App\Models\Supply;
use App\Models\Transfer;
use App\Http\Resources\DisposalResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    public function index(Request $request){
        return inertia('Report/Index');
    } 

    public function inventoryReport(Request $request){
        // Get month parameter or default to current month
        $monthYear = $request->input('month_year', Carbon::now()->format('Y-m'));
        $prevMonthYear = Carbon::createFromFormat('Y-m', $monthYear)->subMonth()->format('Y-m');
        $warehouseId = $request->input('warehouse_id');
        
        // Get all warehouses for the filter dropdown
        $warehouses = Warehouse::orderBy('name')->get(['id', 'name']);
        
        // Get inventory report data
        $reportData = $this->getInventoryReportData($monthYear, $prevMonthYear, $warehouseId);
        
        return inertia('Report/InventoryReport', [
            'reportData' => $reportData,
            'warehouses' => $warehouses,
            'filters' => [
                'month_year' => $monthYear,
                'warehouse_id' => $warehouseId,
            ],
        ]);
    }
    
    /**
     * Get inventory report data for a specific month and optional warehouse
     * 
     * @param string $monthYear Format: YYYY-MM
     * @param string $prevMonthYear Format: YYYY-MM
     * @param int|null $warehouseId
     * @return array
     */
    private function getInventoryReportData($monthYear, $prevMonthYear, $warehouseId = null)
    {
        // Check if we have stored reports for this month
        $storedReport = MonthlyInventoryReport::where('month_year', $monthYear)->first();
            
        // If we have a stored report, use it
        if ($storedReport) {
            // Since we no longer store product-specific data, we'll need to get products separately
            $products = DB::table('products')
                ->join('inventories', 'products.id', '=', 'inventories.product_id')
                ->when($warehouseId, function ($query) use ($warehouseId) {
                    return $query->where('inventories.warehouse_id', $warehouseId);
                })
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'inventories.uom',
                    'inventories.unit_cost'
                )
                ->distinct()
                ->get();
                
            $result = [];
            
            foreach ($products as $product) {
                $result[] = [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'beginning_balance' => $storedReport->beginning_balance,
                    'stock_received' => $storedReport->stock_received,
                    'stock_issued' => $storedReport->stock_issued,
                    'negative_adjustment' => $storedReport->negative_adjustment,
                    'positive_adjustment' => $storedReport->positive_adjustment,
                    'closing_balance' => $storedReport->closing_balance,
                    'uom' => $product->uom ?? 'N/A',
                    'unit_cost' => $product->unit_cost ?? 0,
                    'total_value' => ($storedReport->closing_balance * ($product->unit_cost ?? 0))
                ];
            }
            
            return $result;
        }
        
        // If no stored reports, calculate dynamically
        // Get start and end dates for the selected month
        $startDate = $monthYear . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        // Build query for products with inventory
        $query = DB::table('products')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                DB::raw('COALESCE(inventories.uom, "N/A") as uom'),
                DB::raw('COALESCE(AVG(inventories.unit_cost), 0) as unit_cost')
            )
            ->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
            ->groupBy('products.id', 'products.name', 'inventories.uom');
        
        // Apply warehouse filter if provided
        if ($warehouseId) {
            $query->where('inventories.warehouse_id', $warehouseId);
        }
        
        $products = $query->get();
        
        // Prepare result array
        $result = [];
        
        foreach ($products as $product) {
            // Get previous month closing balance as beginning balance
            $beginningBalance = DB::table('inventories')
                ->where('product_id', $product->product_id)
                ->when($warehouseId, function ($query) use ($warehouseId) {
                    return $query->where('warehouse_id', $warehouseId);
                })
                ->where('created_at', '<', $startDate)
                ->sum('quantity');
            
            // Get stock received this month
            $stockReceivedQuery = DB::table('received_quantity_items')
                ->where('received_quantity_items.product_id', $product->product_id)
                ->whereBetween('received_quantity_items.received_at', [$startDate, $endDate]);
                
            // Apply warehouse filter if provided - warehouse_id might be in the transfer table
            if ($warehouseId) {
                $stockReceivedQuery->join('transfers', 'received_quantity_items.transfer_id', '=', 'transfers.id')
                    ->where('transfers.destination_id', $warehouseId);
            }
            
            $stockReceived = $stockReceivedQuery->sum('received_quantity_items.quantity');
            
            // Get stock issued this month
            $stockIssued = DB::table('issue_quantity_items')
                ->join('issue_quantity_reports', 'issue_quantity_items.parent_id', '=', 'issue_quantity_reports.id')
                ->where('issue_quantity_items.product_id', $product->product_id)
                ->when($warehouseId, function ($query) use ($warehouseId) {
                    return $query->where('issue_quantity_items.warehouse_id', $warehouseId);
                })
                ->where('issue_quantity_reports.month_year', $monthYear)
                ->sum('issue_quantity_items.quantity');
            
            // Get negative adjustments this month (where difference < 0)
            // Note: inventory_adjustments doesn't have a warehouse_id field directly
            // We'll need to join with inventories to filter by warehouse
            $negativeAdjustmentQuery = DB::table('inventory_adjustments')
                ->where('inventory_adjustments.product_id', $product->product_id)
                ->whereBetween('inventory_adjustments.adjustment_date', [$startDate, $endDate])
                ->where('inventory_adjustments.difference', '<', 0)
                ->where('inventory_adjustments.status', 'approved');
                
            // Apply warehouse filter if provided
            if ($warehouseId) {
                // We need to join with inventories to filter by warehouse
                // Using the batch_number as a common identifier
                $negativeAdjustmentQuery->join('inventories', function($join) use ($warehouseId) {
                    $join->on('inventory_adjustments.product_id', '=', 'inventories.product_id')
                         ->on('inventory_adjustments.batch_number', '=', 'inventories.batch_number')
                         ->where('inventories.warehouse_id', '=', $warehouseId);
                });
            }
            
            $negativeAdjustment = $negativeAdjustmentQuery->sum(DB::raw('ABS(inventory_adjustments.difference)'));
            
            // Get positive adjustments this month (where difference > 0)
            $positiveAdjustmentQuery = DB::table('inventory_adjustments')
                ->where('inventory_adjustments.product_id', $product->product_id)
                ->whereBetween('inventory_adjustments.adjustment_date', [$startDate, $endDate])
                ->where('inventory_adjustments.difference', '>', 0)
                ->where('inventory_adjustments.status', 'approved');
                
            // Apply warehouse filter if provided
            if ($warehouseId) {
                // We need to join with inventories to filter by warehouse
                // Using the batch_number as a common identifier
                $positiveAdjustmentQuery->join('inventories', function($join) use ($warehouseId) {
                    $join->on('inventory_adjustments.product_id', '=', 'inventories.product_id')
                         ->on('inventory_adjustments.batch_number', '=', 'inventories.batch_number')
                         ->where('inventories.warehouse_id', '=', $warehouseId);
                });
            }
            
            $positiveAdjustment = $positiveAdjustmentQuery->sum('inventory_adjustments.difference');
            
            // Calculate closing balance
            $closingBalance = $beginningBalance + $stockReceived - $stockIssued - $negativeAdjustment + $positiveAdjustment;
            
            // Calculate total value
            $totalValue = $closingBalance * $product->unit_cost;
            
            // Add to result array
            $result[] = [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'beginning_balance' => $beginningBalance,
                'stock_received' => $stockReceived,
                'stock_issued' => $stockIssued,
                'negative_adjustment' => $negativeAdjustment,
                'positive_adjustment' => $positiveAdjustment,
                'closing_balance' => $closingBalance,
                'uom' => $product->uom,
                'unit_cost' => $product->unit_cost,
                'total_value' => $totalValue
            ];
        }
        
        return $result;
    }
    
    /**
     * API endpoint to get inventory report data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inventoryReportData(Request $request)
    {
        // Validate request
        $request->validate([
            'month_year' => 'required|string', // Format: YYYY-MM
            'prev_month_year' => 'required|string', // Format: YYYY-MM
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        // Get parameters
        $monthYear = $request->input('month_year');
        $prevMonthYear = $request->input('prev_month_year');
        $warehouseId = $request->input('warehouse_id');
        
        // Get inventory report data
        $result = $this->getInventoryReportData($monthYear, $prevMonthYear, $warehouseId);
        
        return response()->json($result);
    }
    
    /**
     * Manually generate inventory report for a specific month
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateInventoryReport(Request $request)
    {
        // Validate request
        $request->validate([
            'month_year' => 'required|string|date_format:Y-m',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);
        
        $monthYear = $request->input('month_year');
        $warehouseId = $request->input('warehouse_id');
        
        // Use Artisan to call the command
        $command = 'report:generate-inventory ' . $monthYear;
        
        if ($warehouseId) {
            $command .= ' --warehouse_id=' . $warehouseId;
        }
        
        try {
            \Illuminate\Support\Facades\Artisan::call($command);
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            // Log the output
            \Illuminate\Support\Facades\Log::info('Manual inventory report generation: ' . $output);
            
            return redirect()->back()->with('success', 'Inventory report generated successfully for ' . $monthYear);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error generating inventory report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error generating inventory report: ' . $e->getMessage());
        }
    }

    public function updatePhysicalCountReport(Request $request){
        try {
            $request->validate([
                'id' => 'required|exists:inventory_adjustments,id',
                'items' => 'required|array',
                'items.*.id' => 'required|exists:inventory_adjustment_items,id',
                'items.*.physical_count' => 'required|numeric',
                'items.*.difference' => 'required',
                'items.*.remarks' => 'nullable',
            ]);
            
            return DB::transaction(function () use ($request) {
                $adjustment = InventoryAdjustment::findOrFail($request->id);
                foreach ($request->items as $item) {
                    $adjustmentItem = InventoryAdjustmentItem::findOrFail($item['id']);
                    $adjustmentItem->update([
                        'physical_count' => $item['physical_count'],
                        'difference' => $item['difference'],
                        'remarks' => $item['remarks']
                    ]);
                }
                
                $adjustment->update([
                    'status' => 'submitted'
                ]);

                // Send email notification to users with report.physical-count-review permission
                $users = User::permission('report.physical-count-review')->get();
                $approvalLink = route('reports.physicalCount', ['month_year' => $adjustment->month_year]);
                $submittedBy = Auth::user();

                foreach ($users as $user) {
                    Mail::to($user->email)
                        ->queue(new PhysicalCountSubmitted($adjustment, $approvalLink, $submittedBy));
                }

                return response()->json("Physical count submitted successfully", 200);
            });
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
    
    public function physicalCountReport(Request $request){
        $monthYear = $request->input('month_year', date('Y-m'));
        
        // Check if there's an existing adjustment for this month
        $adjustment = InventoryAdjustment::where('month_year', $monthYear)
            ->with(['reviewer:id,name', 'approver:id,name', 'rejecter:id,name'])
            ->whereIn('status', ['pending', 'reviewed','submitted'])
            ->first();
        
        $adjustmentData = [];
        
        if ($adjustment) {
            // Get all adjustment items with their related product information
            $items = $adjustment->items()
                ->with([
                    'product' => function($query) {
                        $query->select('id', 'name');
                    },
                    'product',
                    'warehouse:id,name',
                    'location:id,location',
                ])
                ->get();
                
            $adjustmentData = [
                'id' => $adjustment->id,
                'month_year' => $adjustment->month_year,
                'adjustment_date' => $adjustment->adjustment_date,
                'status' => $adjustment->status,
                'items' => $items
            ];
        }
        
        return inertia('Report/PhysicalCountReport', [
            'physicalCountReport' => $adjustmentData,
            'currentMonthYear' => $monthYear,
        ]);
    }

    public function issueQuantityReports(Request $request)
    {
        $query = IssueQuantityReport::query()
            ->with(['items.product.dosage', 'items.product.category', 'items.warehouse']);

        // Handle multiple date filters (year and month combinations)
        if ($request->filled('date_filters') && is_array($request->date_filters)) {
            $query->where(function($q) use ($request) {
                foreach ($request->date_filters as $dateFilter) {
                    // If it's a full year-month format (YYYY-MM)
                    if (strlen($dateFilter) === 7) {
                        $q->orWhere('month_year', 'like', $dateFilter . '%');
                    } 
                    // If it's just a year (YYYY)
                    else if (strlen($dateFilter) === 4) {
                        $q->orWhere('month_year', 'like', $dateFilter . '%');
                    }
                }
            });
        } 
        // Backward compatibility for old filter format
        else if ($request->filled('month')) {
            $query->where('month_year', 'like', $request->month . '%');
        }

        $issueQuantityReports = $query->paginate($request->input('per_page', 10), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        $issueQuantityReports->setPath(url()->current()); // Force Laravel to use full URLs

        return Inertia::render('Report/IssueQuantityReports', [
            'issueQuantityReports' => $issueQuantityReports,
            'warehouses' => Warehouse::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'filters' => $request->only(['month', 'per_page']),
        ]);
    }
    
    /**
     * Export all issue quantity reports to Excel
     */
    public function exportIssueQuantityReports(Request $request)
    {
        $query = IssueQuantityReport::query()
            ->with(['items.product.dosage', 'items.product.category', 'items.warehouse']);

        // Apply date filters
        if ($request->filled('date_filters') && is_array($request->date_filters)) {
            $query->where(function($q) use ($request) {
                foreach ($request->date_filters as $dateFilter) {
                    if (strlen($dateFilter) === 7) { // YYYY-MM
                        $q->orWhere('month_year', 'like', $dateFilter . '%');
                    } else if (strlen($dateFilter) === 4) { // YYYY
                        $q->orWhere('month_year', 'like', $dateFilter . '%');
                    }
                }
            });
        }

        $reports = $query->get();

        // Create Excel file
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Month/Year');
        $sheet->setCellValue('B1', 'Total Quantity');
        $sheet->setCellValue('C1', 'Generated By');
        $sheet->setCellValue('D1', 'Generated At');

        // Set data
        $row = 2;
        foreach ($reports as $report) {
            $sheet->setCellValue('A' . $row, $report->month_year);
            $sheet->setCellValue('B' . $row, $report->total_quantity);
            $sheet->setCellValue('C' . $row, $report->generated_by);
            $sheet->setCellValue('D' . $row, $report->created_at);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Create response
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'issue_quantities_report_' . date('Y-m-d') . '.xlsx';

        // Save to temp file and return
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // The exportIssueQuantityReportItems method has been removed as this functionality
    // is now handled client-side using the XLSX library in the Vue component
    
    // mnthly consumption by facilities [AMC]

    public function receivedQuantities(Request $request)
    {
        $query = MonthlyQuantityReceived::query()
            ->with(['items.product.dosage','items.product.category', 'items.receiver', 'items.transfer', 'items.packingList']);

        // Apply filters
        // Warehouse filter removed as warehouse_id doesn't exist in the product table

        // Handle multiple date filters (year and month combinations)
        if ($request->filled('date_filters') && is_array($request->date_filters)) {
            $query->where(function($q) use ($request) {
                foreach ($request->date_filters as $dateFilter) {
                    // If it's a full year-month format (YYYY-MM)
                    if (strlen($dateFilter) === 7) {
                        $q->orWhere('month_year', 'like', $dateFilter . '%');
                    } 
                    // If it's just a year (YYYY)
                    else if (strlen($dateFilter) === 4) {
                        $q->orWhere('month_year', 'like', $dateFilter . '%');
                    }
                }
            });
        } 
        // Backward compatibility for old filter format
        else if ($request->filled('month')) {
            $query->where('month_year', 'like', $request->month . '%');
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
            'filters' => $request->only(['month', 'per_page']),
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

    public function generatePhysicalCountReport(Request $request)
    {
        try {
            // Check if there's already a pending or reviewed adjustment
            $existingAdjustment = InventoryAdjustment::whereIn('status', ['pending', 'reviewed'])
                ->first();
            
            if ($existingAdjustment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot generate new physical count adjustment. There is already a ' . $existingAdjustment->status . ' adjustment from ' . Carbon::parse($existingAdjustment->adjustment_date)->format('M d, Y') . ' that needs to be processed or rejected first.'
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Create parent adjustment record
            $adjustment = new InventoryAdjustment();
            $adjustment->month_year = date('Y-m');
            $adjustment->adjustment_date = Carbon::now();
            $adjustment->status = 'pending';
            $adjustment->save();
            
            // Get all active inventory items
            $inventories = Inventory::where('is_active', true)->get();
            
            // Create adjustment items for each inventory item
            foreach ($inventories as $inventory) {
                $item = new InventoryAdjustmentItem();
                $item->parent_id = $adjustment->id;
                $item->user_id = auth()->id();
                $item->product_id = $inventory->product_id;
                $item->location_id = $inventory->location_id;
                $item->warehouse_id = $inventory->warehouse_id;
                $item->quantity = $inventory->quantity;
                $item->physical_count = 0; // Default to 0, will be updated during physical count
                $item->batch_number = $inventory->batch_number;
                $item->barcode = $inventory->barcode;
                $item->expiry_date = $inventory->expiry_date;
                $item->uom = $inventory->uom;
                $item->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Physical count adjustment has been successfully generated.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the physical count adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePhysicalCountStatus(Request $request){
        try {
            $request->validate([
                'id' => 'required|exists:inventory_adjustments,id',
                'status' => 'required|in:reviewed,approved'
            ]);
            return DB::transaction(function () use ($request) {
                $adjustment = InventoryAdjustment::findOrFail($request->id);
                $adjustment->update([
                    'status' => $request->status,
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now()
                ]);
                return response()->json("Physical count status updated successfully", 200);
            });
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function approvePhysicalCountReport(Request $request){
        try {
            $request->validate([
                'id' => 'required|exists:inventory_adjustments,id',
                'status' => 'required|in:approved'
            ]);
            return DB::transaction(function () use ($request) {
                $adjustment = InventoryAdjustment::findOrFail($request->id);
                if($adjustment->status !== 'reviewed') {
                    return response()->json("Physical count status must be reviewed before approval", 500);
                }
                // update inventory
                $inventoryItems = InventoryAdjustmentItem::where('parent_id', $adjustment->id)->get();
                foreach ($inventoryItems as $item) {
                    $inventory = Inventory::where('product_id', $item->product_id)
                        ->where('batch_number', $item->batch_number)
                        ->where('expiry_date', $item->expiry_date)
                        ->first();
                    if ($inventory) {
                        $inventory->update([
                            'quantity' => $item->physical_count
                        ]);
                    }
                }
                $adjustment->update([
                    'status' => $request->status,
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);
                return response()->json("Physical count approved successfully", 200);
            });
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function rejectPhysicalCountReport(Request $request){
        try {
            $request->validate([
                'id' => 'required|exists:inventory_adjustments,id',
                'status' => 'required|in:rejected',
                'rejection_reason' => 'required'
            ]);
            return DB::transaction(function () use ($request) {
                $adjustment = InventoryAdjustment::findOrFail($request->id);
                if($adjustment->status !== 'reviewed') {
                    return response()->json("Physical count status must be reviewed before rejection", 500);
                }
                $adjustment->update([
                    'status' => $request->status,
                    'rejected_by' => Auth::id(),
                    'rejected_at' => now(),
                    'rejection_reason' => $request->rejection_reason
                ]);
                return response()->json("Physical count marked as rejected.", 200);
            });
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function rollBackRejectPhysicalCountReport(Request $request){
        try {
            $request->validate([
                'id' => 'required|exists:inventory_adjustments,id',
                'status' => 'required|in:pending'
            ]);
            return DB::transaction(function () use ($request) {
                $adjustment = InventoryAdjustment::findOrFail($request->id);
                if($adjustment->status !== 'rejected') {
                    return response()->json("Physical count status must be rejected before rollback", 500);
                }
                $adjustment->update([
                    'status' => $request->status,
                    'rejected_by' => null,
                    'rejected_at' => null,
                    'rejection_reason' => null
                ]);
                return response()->json("Physical count marked as pending.", 200);
            });
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }   

    public function physicalCountShow(Request $request){
        $physicalCountReport = InventoryAdjustment::query()
            ->when($request->filled('month'), function($query) use ($request) {
                $query->where('month_year', $request->month);
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['items.product.dosage', 'items.product.category', 'items.warehouse', 'approver', 'rejecter', 'reviewer'])
            ->paginate($request->input('per_page', 100), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
            
        $physicalCountReport->setPath(url()->current());
        
        return inertia('Report/PhysicalCountShow', [
            'physicalCountReport' => PhysicalCountReportResource::collection($physicalCountReport),
            'filters' => $request->only(['month', 'per_page', 'page']),
        ]);
    }
    
    public function disposals(Request $request){
        logger()->info($request->all());
        $disposals = Disposal::query()
            ->when($request->filled('month'), function($query) use ($request) {
                $date = Carbon::createFromFormat('Y-m', $request->month);
                $query->whereYear('disposed_at', $date->year)
                      ->whereMonth('disposed_at', $date->month);
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['product.dosage', 'product.category', 'approvedBy', 'rejectedBy', 'reviewedBy','disposedBy'])
            ->paginate($request->input('per_page', 2), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        
        $disposals->setPath(url()->current());
        
        return inertia('Report/Disposals', [
            'disposals' => DisposalResource::collection($disposals),
            'filters' => $request->only(['month', 'per_page', 'page']),
        ]);
    }
    
}
