<?php

namespace App\Http\Controllers;

use App\Mail\PhysicalCountSubmitted;
use Illuminate\Support\Facades\Cache;
use App\Models\AvarageMonthlyconsumption;
use App\Models\Location;
use App\Models\Product;
use App\Models\MonthlyQuantityReceived;
use App\Http\Resources\ReceivedQuantityResource;
use App\Models\MonthlyConsumptionReport;
use App\Models\PackingList;
use App\Models\Warehouse;
use App\Http\Resources\PackingListResource;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Collection;
use App\Models\Facility;
use App\Models\Inventory;
use App\Models\InventoryReport;
use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use App\Models\IssueQuantityReport;
use App\Http\Resources\PurchaseOrderResource;
use App\Http\Resources\PhysicalCountReportResource;
use App\Models\Disposal;
use App\Models\Liquidation;
use App\Models\Supply;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\Transfer;
use App\Http\Resources\DisposalResource;
use App\Models\IssueQuantityItem;
use App\Models\ReceivedQuantityItem;
use App\Models\District;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ImportIssueQuantityJob;
use App\Imports\IssueQuantitiyImport;
use App\Models\FacilityMonthlyReport;
use App\Jobs\ProcessIssueQuantityImport;
use App\Exports\WarehouseMonthlyReportExport;

class ReportController extends Controller
{
    public function importIssueQuantity(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'month_year' => 'required|date_format:Y-m'
        ]);
    
        $file = $request->file('file');
        $monthYear = $request->input('month_year');
        $userId = auth()->id();
    
        // Store the file temporarily
        $path = $file->store('temp');
    
        // Dispatch the job
        ProcessIssueQuantityImport::dispatch($path, $monthYear, $userId)
            ->onQueue('imports');
    
        return back()->with('success', 'Import has been queued and will be processed shortly.');
    }

    public function index(Request $request){
        return inertia('Report/Index');
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

    public function inventoryReport(Request $request){
        $query = InventoryReport::query();

        $query->with(['items.product.dosage', 'items.product.category', 'items.warehouse', 'approver', 'rejecter', 'reviewer']);
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
            ->with(['items.product.dosage', 'items.product.category']);

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

        $issueQuantityReports = $query->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        $issueQuantityReports->setPath(url()->current()); // Force Laravel to use full URLs

        return inertia('Report/IssueQuantityReports', [
            'issueQuantityReports' => $issueQuantityReports,
            'products' => Product::orderBy('name')->get(),
            'filters' => $request->only(['month', 'per_page']),
        ]);
    }

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
            ->with(['items.product.dosage','items.product.category']);

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
        
        $receivedQuantities = $query->paginate($request->input('per_page', 1), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

            $receivedQuantities->setPath(url()->current()); // Force Laravel to use full URLs
        

        return inertia('Report/ReceivedQuantities', [
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
        
        $monthlyConsumptionReport = [];        
        // Only fetch data if the form has been submitted with valid filters
        if ($isSubmitted && $facilityId && $startMonth && $endMonth) {
            $monthlyConsumptionReport = MonthlyConsumptionReport::where('facility_id', $facilityId)
                ->with('facility','items.product')
                ->whereBetween('month_year', [$startMonth, $endMonth])
                ->get();
        }

        return inertia('Report/MonthlyConsumption', [
            'pivotData' => $monthlyConsumptionReport,
            'facilities' => Facility::select('id', 'name', 'facility_type')->get(),
            'products' => Product::select('id', 'name')->get(),
            'facilityInfo' => null,
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
    
    /**
     * Generate inventory report data for the given month and year
     *
     * @param Request $request
     * @param string $monthYear Format: Y-m
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getInventoryReportData(Request $request, $monthYear)
    {
        // Get or create the inventory report
        $inventoryReport = InventoryReport::firstOrCreate(
            ['month_year' => $monthYear],
            [
                'status' => 'generated',
                'generated_by' => auth()->id(),
                'generated_at' => now(),
            ]
        );

        // Return items with product relationship - no need for complex calculations
        return $inventoryReport->items()
            ->with(['product' => function($query) {
                $query->select('id', 'name', 'category_id')
                    ->with('category:id,name');
            }])
            ->get();
    }

    public function warehouseMonthlyReport(Request $request)
    {
        try {
            $monthYear = $request->input('month_year', Carbon::now()->format('Y-m'));
            
            // Get inventory report status
            $inventoryReport = InventoryReport::with('submittedBy', 'reviewedBy', 'approvedBy', 'rejectedBy')
                ->where('month_year', $monthYear)
                ->firstOrCreate(
                    ['month_year' => $monthYear],
                    [
                        'status' => 'generated',
                        'generated_by' => auth()->id(),
                        'generated_at' => now(),
                    ]
                );
                
            // Always load data without pagination
            $reportData = $this->getInventoryReportData($request, $monthYear);
            
            return inertia('Report/WarehouseMonthlyReport', [
                'reportData' => $reportData,
                'monthYear' => $monthYear,
                'inventoryReport' => $inventoryReport->load([
                    'submittedBy' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'reviewedBy' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'approvedBy' => function ($query) {
                        $query->select('id', 'name');
                    },
                    'rejectedBy' => function ($query) {
                        $query->select('id', 'name');
                    }
                ]),
                'filters' => $request->only(['month_year', 'per_page', 'page']),
            ]);
            
        } catch (\Throwable $th) {
            Log::error('Warehouse Monthly Report Error: ' . $th->getMessage());
            Log::error($th->getTraceAsString());
            return back()->with('error', 'Failed to load report page: ' . $th->getMessage());
        }
    }

    /**
     * Update inventory report adjustments
     */
    public function updateInventoryReportAdjustments(Request $request)
    {
        try {
            $request->validate([
                'month_year' => 'required|string',
                'adjustments' => 'required|array',
                'adjustments.*.product_id' => 'required|exists:products,id',
                'adjustments.*.positive_adjustment' => 'required|numeric|min:0',
                'adjustments.*.negative_adjustment' => 'required|numeric|min:0',
                'adjustments.*.months_of_stock' => 'nullable|string',
            ]);

            return DB::transaction(function () use ($request) {
                $inventoryReport = InventoryReport::where('month_year', $request->month_year)->first();
                
                if (!$inventoryReport) {
                    return response()->json(['message' => 'Inventory report not found for this month.'], 404);
                }

                if (!$inventoryReport->canBeEdited()) {
                    return response()->json(['message' => 'This report cannot be edited in its current status.'], 403);
                }

                foreach ($request->adjustments as $adjustment) {
                    $reportItem = $inventoryReport->items()
                        ->where('product_id', $adjustment['product_id'])
                        ->first();

                    if ($reportItem) {
                        $closingBalance = $reportItem->beginning_balance 
                            + $reportItem->received_quantity 
                            - $reportItem->issued_quantity 
                            + $adjustment['positive_adjustment'] 
                            - $adjustment['negative_adjustment'];
                            
                        $totalCost = $closingBalance * $reportItem->unit_cost;
                        
                        $updateData = [
                            'positive_adjustment' => $adjustment['positive_adjustment'],
                            'negative_adjustment' => $adjustment['negative_adjustment'],
                            // Update closing balance
                            'closing_balance' => $closingBalance,
                            // Update total cost based on new closing balance and unit cost
                            'total_cost' => $totalCost,
                        ];
                        
                        // Debug: Log current and new months_of_stock values
                        $oldMonthsOfStock = $reportItem->months_of_stock;
                        $newMonthsOfStock = $adjustment['months_of_stock'] ?? null;
                        
                        Log::debug('Updating months_of_stock', [
                            'product_id' => $reportItem->product_id,
                            'old_value' => $oldMonthsOfStock,
                            'new_value' => $newMonthsOfStock,
                            'type_old' => gettype($oldMonthsOfStock),
                            'type_new' => gettype($newMonthsOfStock),
                            'full_request' => $adjustment
                        ]);
                        
                        // Only update months_of_stock if it's provided in the request
                        if (isset($adjustment['months_of_stock'])) {
                            $updateData['months_of_stock'] = $adjustment['months_of_stock'];
                        }
                        
                        $reportItem->update($updateData);
                        
                        // Log after update to verify
                        $updatedItem = $reportItem->fresh();
                        Log::debug('After update months_of_stock', [
                            'product_id' => $reportItem->product_id,
                            'saved_value' => $updatedItem->months_of_stock,
                            'type_saved' => gettype($updatedItem->months_of_stock)
                        ]);
                    }
                }

                return response()->json(['message' => 'Adjustments updated successfully.'], 200);
            });

        } catch (\Throwable $th) {
            Log::error('Update Inventory Report Adjustments Error: ' . $th->getMessage());
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * Submit inventory report for review
     */
    public function submitInventoryReport(Request $request)
    {
        try {
            $request->validate([
                'month_year' => 'required|string',
            ]);

            $inventoryReport = InventoryReport::where('month_year', $request->month_year)->firstOrFail();

            if ($inventoryReport->status !== 'generated') {
                return response()->json(['message' => 'Only generated reports can be submitted.'], 403);
            }

            $inventoryReport->update([
                'status' => 'submitted',
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),
            ]);

            return response()->json([
                'message' => 'Report submitted successfully.',
                'status' => 'submitted'
            ]);

        } catch (\Throwable $th) {
            Log::error('Submit Report Error: ' . $th->getMessage());
            return response()->json(['message' => 'Failed to submit report.'], 500);
        }
    }

    /**
     * Review inventory report
     */
    public function reviewInventoryReport(Request $request)
    {
        try {
            $request->validate([
                'month_year' => 'required|string',
            ]);

            $inventoryReport = InventoryReport::where('month_year', $request->month_year)->firstOrFail();

            if ($inventoryReport->status !== 'submitted') {
                return response()->json(['message' => 'Only submitted reports can be reviewed.'], 403);
            }

            $inventoryReport->update([
                'status' => 'under_review',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Report marked as under review.',
                'status' => 'under_review'
            ]);

        } catch (\Throwable $th) {
            Log::error('Review Report Error: ' . $th->getMessage());
            return response()->json(['message' => 'Failed to review report.'], 500);
        }
    }

    /**
     * Approve inventory report
     */
    public function approveInventoryReport(Request $request)
    {
        try {
            $request->validate([
                'month_year' => 'required|string',
            ]);

            $inventoryReport = InventoryReport::where('month_year', $request->month_year)->firstOrFail();

            if ($inventoryReport->status !== 'under_review') {
                return response()->json(['message' => 'Only reports under review can be approved.'], 403);
            }

            $inventoryReport->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return response()->json([
                'message' => 'Report approved successfully.',
                'status' => 'approved'
            ]);

        } catch (\Throwable $th) {
            Log::error('Approve Report Error: ' . $th->getMessage());
            return response()->json(['message' => 'Failed to approve report.'], 500);
        }
    }

    /**
     * Reject inventory report
     */
    public function rejectInventoryReport(Request $request)
    {
        try {
            $request->validate([
                'month_year' => 'required|string',
                'reason' => 'nullable|string|max:500',
            ]);

            $inventoryReport = InventoryReport::where('month_year', $request->month_year)->firstOrFail();

            if ($inventoryReport->status !== 'under_review') {
                return response()->json(['message' => 'Only reports under review can be rejected.'], 403);
            }

            $inventoryReport->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->reason,
            ]);

            return response()->json([
                'message' => 'Report rejected successfully.',
                'status' => 'rejected'
            ]);

        } catch (\Throwable $th) {
            Log::error('Reject Report Error: ' . $th->getMessage());
            return response()->json(['message' => 'Failed to reject report.'], 500);
        }
    }

    public function orders(Request $request)
    {
        // Get facilities for dropdown
        $facilities = Facility::get()->pluck('name')->toArray();
    
        $query = Order::query();
    
        // Eager load nested relationships
        $query->with([
            'items.inventory_allocations.back_order',
            'items.inventory_allocations.product:id,name',
            'items.inventory_allocations.warehouse',
            'items.inventory_allocations.location',
            'facility',
            'user',
            'approvedBy',
            'rejectedBy',
            'dispatchedBy'
        ]);
    
        // Filters
        if ($request->filled('facility')) {
            $query->whereHas('facility', function ($q) use ($request) {
                $q->where('name', $request->facility);
            });
        }
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereDate('order_date', $request->date_from);
        }
    
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('order_date', [$request->date_from, $request->date_to]);
        }
    
        $orders = $query->paginate(
            $request->input('per_page', 25),
            ['*'],
            'page',
            $request->input('page', 1)
        )->withQueryString();
    
        // Transform orders: extract inventory_allocations, remove items
        $orders->getCollection()->transform(function ($order) {
            $inventoryAllocations = collect();
    
            foreach ($order->items as $item) {
                foreach ($item->inventory_allocations as $alloc) {
                    $inventoryAllocations->push($alloc);
                }
            }
    
            // Remove items relation and add top-level inventory_allocations
            $order->unsetRelation('items');
            $order->inventory_allocations = $inventoryAllocations;
    
            return $order;
        });
    
        // Set full path to keep proper pagination links
        $orders->setPath(url()->current());
    
        return inertia('Report/Orders', [
            'orders' => $orders,
            'filters' => $request->only('facility', 'status', 'per_page', 'page', 'date_from', 'date_to'),
            'facilities' => $facilities
        ]);
    }

    public function orderTracking(Request $request)
    {
        // Get facilities for dropdown
        $facilities = Facility::get()->pluck('name')->toArray();
    
        $query = Order::query();
    
        // Eager load order-level relationships and items with full information
        $query->with([
            'facility.handledby',
            'createdBy',
            'approvedBy',
            'rejectedBy',
            'dispatchedBy',
            'items.product:id,name,dosage_id',
            'items.product.dosage:id,name',
            'items.warehouse:id,name',
            'items.inventory_allocations.product:id,name',
            'items.inventory_allocations.warehouse:id,name',
            'items.inventory_allocations.location:id,location'
        ]);
    
        // Filters
        if ($request->filled('facility')) {
            $query->whereHas('facility', function ($q) use ($request) {
                $q->where('name', $request->facility);
            });
        }
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereDate('order_date', $request->date_from);
        }
    
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('order_date', [$request->date_from, $request->date_to]);
        }
    
        $orders = $query->paginate(
            $request->input('per_page', 25),
            ['*'],
            'page',
            $request->input('page', 1)
        )->withQueryString();
    
        // Transform orders with order-level tracking data
        $orders->getCollection()->transform(function ($order) {
            // Calculate order-level metrics using only OrderItem fields
            $orderStats = DB::table('order_items')
                ->where('order_items.order_id', $order->id)
                ->selectRaw('
                    SUM(order_items.quantity_to_release) as total_allocated,
                    SUM(COALESCE(order_items.received_quantity, 0)) as total_received
                ')
                ->first();

            $totalAllocated = $orderStats->total_allocated ?? 0;
            $totalReceived = $orderStats->total_received ?? 0;
            $fulfillmentPercentage = $totalAllocated > 0 ? round(($totalReceived / $totalAllocated) * 100) : 0;

            $order->tracking_data = [
                'total_allocated' => $totalAllocated,
                'total_received' => $totalReceived,
                'fulfillment_percentage' => $fulfillmentPercentage,
            ];

            return $order;
        });
    
        // Set full path to keep proper pagination links
        $orders->setPath(url()->current());
    
        return inertia('Report/OrderTracking', [
            'orders' => $orders,
            'filters' => $request->only('facility', 'status', 'per_page', 'page', 'date_from', 'date_to'),
            'facilities' => $facilities
        ]);
    }

    public function orderFulfillment(Request $request)
    {
        // Get facilities for dropdown
        $facilities = Facility::get()->pluck('name')->toArray();
    
        $query = Order::query();
    
        // Eager load nested relationships for fulfillment analysis
        $query->with([
            'items.inventory_allocations.back_order',
            'items.inventory_allocations.product:id,name',
            'items.inventory_allocations.warehouse',
            'items.inventory_allocations.location',
            'facility.handledby',
            'user',
            'approvedBy',
            'rejectedBy',
            'dispatchedBy'
        ]);
    
        // Filters
        if ($request->filled('facility')) {
            $query->whereHas('facility', function ($q) use ($request) {
                $q->where('name', $request->facility);
            });
        }
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereDate('order_date', $request->date_from);
        }
    
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('order_date', [$request->date_from, $request->date_to]);
        }
    
        $orders = $query->paginate(
            $request->input('per_page', 25),
            ['*'],
            'page',
            $request->input('page', 1)
        )->withQueryString();
    
        // Transform orders: extract inventory_allocations, remove items, and add tracking_data
        $orders->getCollection()->transform(function ($order) {
            $inventoryAllocations = collect();
            $totalAllocated = 0;
            $totalReceived = 0;
    
            foreach ($order->items as $item) {
                foreach ($item->inventory_allocations as $alloc) {
                    $inventoryAllocations->push($alloc);
                }
                // Use OrderItem fields directly
                $totalAllocated += $item->quantity_to_release ?? 0;
                $totalReceived += $item->received_quantity ?? 0;
            }
    
            // Calculate fulfillment percentage
            $fulfillmentPercentage = $totalAllocated > 0 ? round(($totalReceived / $totalAllocated) * 100) : 0;
    
            // Remove items relation and add top-level inventory_allocations
            $order->unsetRelation('items');
            $order->inventory_allocations = $inventoryAllocations;
            
            // Add tracking_data with fulfillment metrics
            $order->tracking_data = [
                'total_allocated' => $totalAllocated,
                'total_received' => $totalReceived,
                'fulfillment_percentage' => $fulfillmentPercentage,
            ];
    
            return $order;
        });
    
        // Set full path to keep proper pagination links
        $orders->setPath(url()->current());

        // Calculate fulfillment metrics
        $fulfillmentMetrics = $this->calculateFulfillmentMetrics($query->get());
    
        return inertia('Report/OrderFulfillment', [
            'orders' => $orders,
            'filters' => $request->only('facility', 'status', 'per_page', 'page', 'date_from', 'date_to'),
            'facilities' => $facilities,
            'fulfillmentMetrics' => $fulfillmentMetrics,
        ]);
    }

    private function calculateFulfillmentMetrics($orders)
    {
        $totalOrders = $orders->count();
        $totalItems = 0;
        $totalAllocated = 0;
        $totalReceived = 0;

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $totalItems += $item->quantity;
                
                foreach ($item->inventory_allocations as $allocation) {
                    $totalAllocated += $allocation->allocated_inventory ?? 0;
                    $totalReceived += $allocation->received_quantity ?? 0;
                }
            }
        }

        $allocationRate = $totalItems > 0 ? ($totalAllocated / $totalItems) * 100 : 0;
        $fulfillmentRate = $totalItems > 0 ? ($totalReceived / $totalItems) * 100 : 0;
        $efficiencyRate = $totalAllocated > 0 ? ($totalReceived / $totalAllocated) * 100 : 0;

        return [
            'totalOrders' => $totalOrders,
            'totalItems' => $totalItems,
            'totalAllocated' => $totalAllocated,
            'totalReceived' => $totalReceived,
            'allocationRate' => round($allocationRate, 2),
            'fulfillmentRate' => round($fulfillmentRate, 2),
            'efficiencyRate' => round($efficiencyRate, 2),
        ];
    }

    public function transfers(Request $request)
    {
        // Get facilities for dropdown
        $facilities = Facility::get()->pluck('name')->toArray();
        $warehouses = Warehouse::get()->pluck('name')->toArray();

        logger()->info($request->all());
    
        $query = Transfer::query();
    
        // Eager load nested relationships
        $query->with([
            'items.product',
            'toFacility',
            'fromFacility',
            'toWarehouse',
            'fromWarehouse',
            'createdBy',
            'approvedBy',
            'rejectedBy',
            'dispatchedBy'
        ]);
    
        // Filters
        if ($request->filled('facility')) {
            $query->whereHas('toFacility', function ($q) use ($request) {
                $q->where('name', $request->facility);
            })->orWhereHas('fromFacility', function ($q) use ($request) {
                $q->where('name', $request->facility);
            });
        }

        // warehouses
        if ($request->filled('warehouse')) {
            $query->whereHas('toWarehouse', function ($q) use ($request) {
                $q->where('name', $request->warehouse);
            })->orWhereHas('fromWarehouse', function ($q) use ($request) {
                $q->where('name', $request->warehouse);
            });
        }
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('date_from') && !$request->filled('date_to')) {
            $query->whereDate('transfer_date', $request->date_from);
        }
    
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('transfer_date', [$request->date_from, $request->date_to]);
        }
    
        $transfers = $query->paginate(
            $request->input('per_page', 25),
            ['*'],
            'page',
            $request->input('page', 1)
        )->withQueryString();
    
        // Set full path to keep proper pagination links
        $transfers->setPath(url()->current());
    
        return inertia('Report/Transfers', [
            'transfers' => $transfers,
            'filters' => $request->only('facility','warehouse', 'status', 'per_page', 'page', 'date_from', 'date_to'),
            'facilities' => $facilities,
            'warehouses' => $warehouses
        ]);
    }


    // purchase orders
    public function purchaseOrders(Request $request)
    {
        $suppliers = Supplier::get()->pluck('name')->toArray();
    
        $purchaseOrdersQuery = PurchaseOrder::query();
    
        if ($request->filled('supplier')) {
            $purchaseOrdersQuery->whereHas('supplier', function($query) use ($request) {
                $query->where('name', $request->supplier);
            });
        }
    
        if ($request->filled('status')) {
            $purchaseOrdersQuery->where('status', $request->status);
        }
    
        if ($request->filled('date_from') && !$request->filled('date_to')) {
            $purchaseOrdersQuery->whereDate('po_date', $request->date_from);
        }
    
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $purchaseOrdersQuery->whereBetween('po_date', [$request->date_from, $request->date_to]);
        }
    
        // ✅ Now assign the result of paginate() to a variable
        $purchaseOrders = $purchaseOrdersQuery
            ->with(['items.product.dosage', 'items.product.category', 'supplier', 'creator', 'approvedBy', 'rejectedBy', 'reviewedBy'])
            ->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        $purchaseOrders->setPath(url()->current());

    
        return inertia('Report/PurchaseOrder', [
            'suppliers' => $suppliers,
            'purchaseOrders' => PurchaseOrderResource::collection($purchaseOrders),
            'filters' => $request->only('per_page', 'page', 'supplier', 'date_from', 'date_to', 'status')
        ]);
    }

    // packing list
    public function packingList(Request $request)
    {
        $supplier = Supplier::get()->pluck('name')->toArray();
        $packingLists = PackingList::query();

        if ($request->filled('search')) {
            $packingLists->whereHas('purchaseOrder', function($query) use ($request) {
                $query->where('ref_no', $request->search)
                ->orWhere('po_number', $request->search);
            })
            ->orWhere('ref_no', $request->search)
            ->orWhere('packing_list_number', $request->search);
        }

        if ($request->filled('supplier')) {
            $packingLists->whereHas('purchaseOrder.supplier', function($query) use ($request) {
                $query->where('name', $request->supplier);
            });
        }

        if ($request->filled('status')) {
            $packingLists->where('status', $request->status);
        }

        if ($request->filled('date_from') && !$request->filled('date_to')) {
            $packingLists->whereDate('pk_date', $request->date_from);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $packingLists->whereBetween('pk_date', [$request->date_from, $request->date_to]);
        }

        // ✅ Now assign the result of paginate() to a variable
        $packingLists = $packingLists
            ->with(['items.product.dosage','items.warehouse','items.location', 'items.product.category', 'purchaseOrder.supplier', 'confirmedBy', 'approvedBy', 'rejectedBy', 'reviewedBy'])
            ->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        $packingLists->setPath(url()->current());

        return inertia('Report/PackingList', [
            'suppliers' => $supplier,
            'packingLists' => PackingListResource::collection($packingLists),
            'filters' => $request->only('search','per_page', 'page', 'supplier', 'date_from', 'date_to', 'status','purchaser_order')
        ]);
    }

    public function lmisMonthlyReport(Request $request){
        // Group facilities by their district name
        $facilities = Facility::select('id', 'name', 'district')->get()
            ->groupBy('district')
            ->map(function ($group) {
                return $group->values(); // reset array keys
            });

        $report = FacilityMonthlyReport::where('report_period', $request->month_year)
            ->with('items.product.category','facility','submittedBy','reviewedBy','approvedBy','rejectedBy')->first();
    
        return inertia('Report/LMISMonthlyReport', [
            'facilitiesGrouped' => $facilities,
            'report' => $report,
            'filters' => $request->only('facility', 'month_year')
        ]);
    }

    public function export($monthYear, Request $request)
    {
        $format = $request->input('format', 'excel');
        $report = InventoryReport::where('month_year', $monthYear)->firstOrFail();
        $report->load([
            'items.inventory_allocations.product.category',
        ]);

        if ($format === 'pdf') {
            return PDF::download(
                new OrderReportPdf($report),
                'orders_' . $monthYear . '.pdf'
            );
        }

        return Excel::download(
            new OrderReportExport($report),
            'orders_' . $monthYear . '.xlsx'
        );
    }

    /**
     * Export orders to Excel
     */
    public function exportOrdersToExcel(Request $request)
    {
        $filters = $request->validate([
            'month_year' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'status' => ['nullable', 'string', 'in:pending,approved,rejected,delivered,cancelled'],
        ]);

        return Excel::download(new OrderExport($filters), 'orders_' . $filters['month_year'] . '.xlsx');
    }

    /**
     * Export warehouse monthly report to Excel
     */
    public function exportToExcel($monthYear, Request $request)
    {
        try {
            $reportData = $this->getInventoryReportData($request, $monthYear);
            
            $filename = "warehouse_monthly_report_{$monthYear}.xlsx";
            
            return Excel::download(new WarehouseMonthlyReportExport($reportData, $monthYear), $filename);
            
        } catch (\Throwable $th) {
            Log::error('Export Error: ' . $th->getMessage());
            return back()->with('error', 'Failed to export report: ' . $th->getMessage());
        }
    }

    public function exportOrderTrackingExcel(Request $request)
    {
        $filters = $request->only(['facility', 'status', 'date_from', 'date_to', 'per_page', 'page']);
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\OrderTrackingExport($filters), 'order_tracking_report.xlsx');
    }

    /**
     * Active & Inactive Product Report
     */
    public function activeInactiveProducts(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'dosage', 'subCategory']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by category
        if ($request->filled('category_ids') && is_array($request->category_ids)) {
            $categoryIds = collect($request->category_ids)->pluck('id')->filter();
            if ($categoryIds->isNotEmpty()) {
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Filter by dosage
        if ($request->filled('dosage_ids') && is_array($request->dosage_ids)) {
            $dosageIds = collect($request->dosage_ids)->pluck('id')->filter();
            if ($dosageIds->isNotEmpty()) {
                $query->whereIn('dosage_id', $dosageIds);
            }
        }

        // Search by product name or ID
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('productID', 'like', '%' . $request->search . '%');
            });
        }

        // Get counts for summary cards (before pagination)
        $activeCount = (clone $query)->where('is_active', true)->count();
        $inactiveCount = (clone $query)->where('is_active', false)->count();
        $totalCount = (clone $query)->count();

        $products = $query->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $products->setPath(url()->current()); // Force Laravel to use full URLs

        return inertia('Report/Products/ActiveInactive', [
            'products' => $products,
            'categories' => \App\Models\Category::where('is_active', true)->get(),
            'dosages' => \App\Models\Dosage::where('is_active', true)->get(),
            'filters' => $request->only(['status', 'category_ids', 'dosage_ids', 'search', 'per_page']),
            'summary' => [
                'active_count' => $activeCount,
                'inactive_count' => $inactiveCount,
                'total_count' => $totalCount
            ]
        ]);
    }

    /**
     * Product Eligibility Report
     */
    public function productEligibility(Request $request)
    {
        $query = \App\Models\EligibleItem::query()
            ->with(['product.category', 'product.dosage']);

        // Filter by facility type
        if ($request->filled('facility_type')) {
            $query->where('facility_type', $request->facility_type);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $eligibleItems = $query->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $eligibleItems->setPath(url()->current()); // Force Laravel to use full URLs

        // Get unique facility types for filter
        $facilityTypes = \App\Models\EligibleItem::distinct('facility_type')
            ->pluck('facility_type')
            ->map(function($type) {
                return [
                    'value' => $type,
                    'label' => ucwords(str_replace('_', ' ', $type))
                ];
            });

        return inertia('Report/Products/Eligibility', [
            'eligibleItems' => $eligibleItems,
            'products' => \App\Models\Product::where('is_active', true)->get(),
            'categories' => \App\Models\Category::where('is_active', true)->get(),
            'facilityTypes' => $facilityTypes,
            'filters' => $request->only(['facility_type', 'product_id', 'category_id', 'per_page'])
        ]);
    }

    /**
     * Product Category Report
     */
    public function productCategories(Request $request)
    {
        $query = \App\Models\Category::query()
            ->withCount(['products as total_products'])
            ->withCount(['products as active_products' => function($query) {
                $query->where('is_active', true);
            }])
            ->withCount(['products as inactive_products' => function($query) {
                $query->where('is_active', false);
            }]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search by category name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $categories->setPath(url()->current()); // Force Laravel to use full URLs

        // Get products for each category
        $categories->getCollection()->transform(function($category) {
            $category->products = $category->products()
                ->with(['dosage', 'subCategory'])
                ->get();
            return $category;
        });

        return inertia('Report/Products/Categories', [
            'categories' => $categories,
            'filters' => $request->only(['status', 'search', 'per_page'])
        ]);
    }

    /**
     * Product Dosage Forms Report
     */
    public function productDosageForms(Request $request)
    {
        $query = \App\Models\Dosage::query()
            ->withCount(['products as total_products'])
            ->withCount(['products as active_products' => function($query) {
                $query->where('is_active', true);
            }])
            ->withCount(['products as inactive_products' => function($query) {
                $query->where('is_active', false);
            }]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search by dosage name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $dosages = $query->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $dosages->setPath(url()->current()); // Force Laravel to use full URLs

        // Get products for each dosage
        $dosages->getCollection()->transform(function($dosage) {
            $dosage->products = $dosage->products()
                ->with(['category', 'subCategory'])
                ->get();
            return $dosage;
        });

        return inertia('Report/Products/DosageForms', [
            'dosages' => $dosages,
            'filters' => $request->only(['status', 'search', 'per_page'])
        ]);
    }
}
