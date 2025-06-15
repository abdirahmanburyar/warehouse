<?php

namespace App\Http\Controllers;

use App\Mail\PhysicalCountSubmitted;
use App\Models\AvarageMonthlyconsumption;
use App\Models\Location;
use App\Models\Product;
use App\Models\MonthlyQuantityReceived;
use App\Http\Resources\ReceivedQuantityResource;
use App\Models\MonthlyConsumptionReport;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Order;
use App\Models\Facility;
use App\Models\Inventory;
use App\Models\InventoryReport;
use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use App\Models\IssueQuantityReport;
use App\Http\Resources\PhysicalCountReportResource;
use App\Models\Disposal;
use App\Models\Liquidation;
use App\Models\Supply;
use App\Models\Transfer;
use App\Http\Resources\DisposalResource;
use App\Models\IssueQuantityItem;
use App\Models\ReceivedQuantityItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ImportIssueQuantityJob;
use App\Imports\IssueQuantitiyImport;

class ReportController extends Controller
{
    public function importIssueQuantity(Request $request)
    {
        try {
            $request->validate([
                'month_year' => 'required|string',
                'file' => 'required|file|mimes:xlsx,xls',
            ]);
            
            $userId = Auth::id();
            
            Excel::queueImport(new IssueQuantitiyImport($request->month_year, $userId), $request->file('file'));

            return response()->json('Import queued successfully.', 200);
        } catch (\Throwable $th) {
            Log::error('Import error', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            return response()->json($th->getMessage(), 500);
        }
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
            ->with(['items.product.dosage', 'items.product.category', 'items.warehouse','items.issuer']);

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

        return inertia('Report/IssueQuantityReports', [
            'issueQuantityReports' => $issueQuantityReports,
            'warehouses' => Warehouse::orderBy('name')->get(),
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
    
    public function warehouseMonthlyReport(Request $request)
    {
        try {
            $monthYear = $request->input('month_year', Carbon::now()->format('Y-m'));
            $perPage = $request->input('per_page', 25);
            $page = $request->input('page', 1);
            
            // Check if we should load data
            $shouldLoadData = $request->has('load_data');
            
            // Get inventory report status
            $inventoryReport = InventoryReport::where('month_year', $monthYear)->first();
            
            if ($shouldLoadData) {
                // Load actual data using InventoryReport and InventoryReportItem
                $reportData = $this->getInventoryReportData($request, $monthYear);
            } else {
                // Create empty paginator as default for initial page load
                $reportData = new \Illuminate\Pagination\LengthAwarePaginator(
                    [], // empty items
                    0,  // total
                    $perPage,
                    $page,
                    [
                        'path' => $request->url(),
                        'pageName' => 'page',
                    ]
                );
                $reportData->appends($request->all());
            }
            
            return inertia('Report/WarehouseMonthlyReport', [
                'reportData' => $reportData,
                'monthYear' => $monthYear,
                'inventoryReport' => $inventoryReport,
                'filters' => $request->only(['month_year', 'per_page', 'page']),
            ]);
            
        } catch (\Throwable $th) {
            Log::error('Warehouse Monthly Report Error: ' . $th->getMessage());
            return back()->with('error', 'Failed to load report page. Please try again.');
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
                        $reportItem->update([
                            'positive_adjustment' => $adjustment['positive_adjustment'],
                            'negative_adjustment' => $adjustment['negative_adjustment'],
                            // Recalculate closing balance
                            'closing_balance' => $reportItem->beginning_balance 
                                + $reportItem->received_quantity 
                                - $reportItem->issued_quantity 
                                + $adjustment['positive_adjustment'] 
                                - $adjustment['negative_adjustment']
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
                'month_year' => 'required|string'
            ]);

            return DB::transaction(function () use ($request) {
                $inventoryReport = InventoryReport::where('month_year', $request->month_year)->first();
                
                if (!$inventoryReport) {
                    return response()->json(['message' => 'Inventory report not found for this month.'], 404);
                }

                if (!$inventoryReport->canBeSubmitted()) {
                    return response()->json(['message' => 'This report cannot be submitted in its current status.'], 403);
                }

                $inventoryReport->update([
                    'status' => InventoryReport::STATUS_SUBMITTED
                ]);

                return response()->json(['message' => 'Report submitted successfully for review.'], 200);
            });

        } catch (\Throwable $th) {
            Log::error('Submit Inventory Report Error: ' . $th->getMessage());
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * Review inventory report (change status to under review)
     */
    public function reviewInventoryReport(Request $request)
    {
        try {
            $request->validate([
                'month_year' => 'required|string'
            ]);

            return DB::transaction(function () use ($request) {
                $inventoryReport = InventoryReport::where('month_year', $request->month_year)->first();
                
                if (!$inventoryReport) {
                    return response()->json(['message' => 'Inventory report not found for this month.'], 404);
                }

                if (!$inventoryReport->canBeReviewed()) {
                    return response()->json(['message' => 'This report cannot be reviewed in its current status.'], 403);
                }

                $inventoryReport->update([
                    'status' => InventoryReport::STATUS_UNDER_REVIEW
                ]);

                return response()->json(['message' => 'Report is now under review.'], 200);
            });

        } catch (\Throwable $th) {
            Log::error('Review Inventory Report Error: ' . $th->getMessage());
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * Approve inventory report
     */
    public function approveInventoryReport(Request $request)
    {
        try {
            $request->validate([
                'month_year' => 'required|string'
            ]);

            return DB::transaction(function () use ($request) {
                $inventoryReport = InventoryReport::where('month_year', $request->month_year)->first();
                
                if (!$inventoryReport) {
                    return response()->json(['message' => 'Inventory report not found for this month.'], 404);
                }

                if (!$inventoryReport->canBeReviewed()) {
                    return response()->json(['message' => 'This report cannot be approved in its current status.'], 403);
                }

                $inventoryReport->update([
                    'status' => InventoryReport::STATUS_APPROVED
                ]);

                return response()->json(['message' => 'Report approved successfully.'], 200);
            });

        } catch (\Throwable $th) {
            Log::error('Approve Inventory Report Error: ' . $th->getMessage());
            return response()->json(['message' => $th->getMessage()], 500);
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
                'reason' => 'nullable|string|max:500'
            ]);

            return DB::transaction(function () use ($request) {
                $inventoryReport = InventoryReport::where('month_year', $request->month_year)->first();
                
                if (!$inventoryReport) {
                    return response()->json(['message' => 'Inventory report not found for this month.'], 404);
                }

                if (!$inventoryReport->canBeReviewed()) {
                    return response()->json(['message' => 'This report cannot be rejected in its current status.'], 403);
                }

                $inventoryReport->update([
                    'status' => InventoryReport::STATUS_REJECTED
                ]);

                return response()->json(['message' => 'Report rejected successfully.'], 200);
            });

        } catch (\Throwable $th) {
            Log::error('Reject Inventory Report Error: ' . $th->getMessage());
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
    
    public function getInventoryReportData(Request $request, $monthYear = null)
    {
        try {
            $monthYear = $monthYear ?: $request->input('month_year', Carbon::now()->format('Y-m'));
            
            // Find the inventory report for the specified month
            $inventoryReport = InventoryReport::where('month_year', $monthYear)
                ->with(['items.product.dosage', 'items.product.category'])
                ->first();
            
            $reportData = [];
            
            if ($inventoryReport && $inventoryReport->items->isNotEmpty()) {
                // Transform the data for the frontend
                foreach ($inventoryReport->items as $item) {
                    $reportData[] = [
                        'id' => $item->id, // Add item ID for updates
                        'product' => $item->product,
                        'beginning_balance' => (float) $item->beginning_balance,
                        'received_quantity' => (float) $item->received_quantity,
                        'issued_quantity' => (float) $item->issued_quantity,
                        'positive_adjustment' => (float) $item->positive_adjustment,
                        'negative_adjustment' => (float) $item->negative_adjustment,
                        'closing_balance' => (float) $item->closing_balance,
                        'batch_number' => $item->batch_number,
                        'expiry_date' => $item->expiry_date,
                        'unit_cost' => (float) $item->unit_cost,
                        'total_cost' => (float) $item->total_cost,
                        'months_of_stock' => (float) $item->months_of_stock,
                    ];
                }
            }
            
            // Paginate the results
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 25);
            $total = count($reportData);
            $offset = ($page - 1) * $perPage;
            $items = array_slice($reportData, $offset, $perPage);
            
            $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );
            $paginatedData->appends($request->all());
            
            return $paginatedData;
            
        } catch (\Throwable $th) {
            Log::error('Get Inventory Report Data Error: ' . $th->getMessage());
            throw $th;
        }
    }
}
