<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\SubLocation;
use App\Models\AssetLocation;
use App\Models\Region;
use App\Models\User;
use App\Models\Assignee;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\CustodyHistory;
use App\Models\FundSource;
use App\Models\AssetAttachment;
use App\Models\AssetApproval;
use App\Http\Resources\AssetResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AssetsImport;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $assets = Asset::query();

        if($request->filled('search')){
            $assets->whereLike('asset_tag', '%'.$request->search.'%')   
                ->orWhereLike('name', '%'.$request->search.'%')
                ->orWhereLike('serial_number', '%'.$request->search.'%')
                ->orWhereHas('fundSource', function($query) use ($request){
                    $query->where('name', 'like', '%'.$request->search.'%');
                });
        }

        if($request->filled('region_id')){
            $assets->where('region_id', $request->region_id);
        }

        if($request->filled('location_id')){
            $assets->where('asset_location_id', $request->location_id);
        }

        if($request->filled('sub_location_ids') && is_array($request->sub_location_ids)){
            $assets->whereIn('sub_location_id', $request->sub_location_ids);
        } elseif($request->filled('sub_location_id')){
            $assets->where('sub_location_id', $request->sub_location_id);
        }

        if($request->filled('fund_source_id')){
            $assets->where('fund_source_id', $request->fund_source_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $assets->where('status', $request->status);
        }

        // New filters
        if ($request->filled('category_id')) {
            $assets->where('asset_category_id', $request->category_id);
        }
        if ($request->filled('type_id')) {
            $assets->where('type_id', $request->type_id);
        }
        if ($request->filled('assignee_id')) {
            $assets->where('assignee_id', $request->assignee_id);
        }
        if ($request->filled('acquisition_from') || $request->filled('acquisition_to')) {
            $from = $request->input('acquisition_from');
            $to = $request->input('acquisition_to');
            if ($from && $to) {
                $assets->whereBetween('acquisition_date', [$from, $to]);
            } elseif ($from) {
                $assets->whereDate('acquisition_date', '>=', $from);
            } elseif ($to) {
                $assets->whereDate('acquisition_date', '<=', $to);
            }
        }
        if ($request->filled('created_from') || $request->filled('created_to')) {
            $from = $request->input('created_from');
            $to = $request->input('created_to');
            if ($from && $to) {
                $assets->whereBetween('created_at', [$from, $to]);
            } elseif ($from) {
                $assets->whereDate('created_at', '>=', $from);
            } elseif ($to) {
                $assets->whereDate('created_at', '<=', $to);
            }
        }

        $assets = $assets->with('category:id,name','type:id,name','assetLocation:id,name', 'subLocation:id,name', 'assignee:id,name','fundSource','region:id,name')
            ->paginate($request->input('per_page', 10), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        $assets->setPath(url()->current()); // Force Laravel to use full URLs

        $count = Asset::count();

        $locations = AssetLocation::with('subLocations')->get();
        $categories = AssetCategory::select('id','name')->get();
        $types = AssetType::select('id','name','asset_category_id')->get();
        $assignees = Assignee::select('id','name')->get();
        return inertia('Assets/Index', [
            'locations' => $locations,
            'assets' => AssetResource::collection($assets),
            'filters' => $request->only('page','per_page','search','region_id','location_id','sub_location_id','fund_source_id','category_id','type_id','assignee_id','acquisition_from','acquisition_to','created_from','created_to','status'),
            'assetsCount' => $count,
            'regions' => Region::get(),
            'fundSources' => FundSource::get(),
            'categories' => $categories,
            'types' => $types,
            'assignees' => $assignees,
        ]);
    }

    public function storeDocument(Request $request)
    {
        try {
            $request->validate([
                'documents' => 'array',
                'documents.*.type' => 'required',
                'documents.*.asset_id' => 'required',
            ]);

            foreach ($request->documents as $document) {
                $asset = Asset::findOrFail($document['asset_id']);
                if (!empty($document['type']) && !empty($document['file'])) {
                    // Handle file upload to storage/app/public/assets/{asset_id}
                    if (is_object($document['file']) && method_exists($document['file'], 'getClientOriginalName')) {
                        $fileName = time() . '_' . $document['file']->getClientOriginalName();
                        $path = $document['file']->storeAs("assets/{$asset->id}", $fileName, 'public');
                        // Create attachment record
                        $asset->attachments()->create([
                            'type' => $document['type'],
                            'file' => "storage/{$path}"
                        ]);
                    }
                }
            }
           
            return response()->json("Successfully uploaded", 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function deleteDocument(Request $request, $id)
    {
        try {
            $document = AssetAttachment::findOrFail($id);

            // Delete the physical file first (if it exists)
            if ($document->file) {
                if (file_exists(public_path($document->file))) {
                    @unlink($document->file);
                }
            }

            // Delete the database record
            $document->delete();

            return response()->json('Document deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function show(Request $request, $id)
    {
        $asset = Asset::with('category:id,name', 'location:id,name', 'subLocation:id,name', 'history', 'attachments', 'fundSource', 'submittedBy')
            ->findOrFail($id);

        return inertia('Assets/Show', [
            'asset' => $asset,
        ]);
    }


    public function getAssets(Request $request)
    {
        $query = Asset::query()
            ->with('category:id,name', 'location:id,name', 'subLocation:id,name', 'history');

        // Apply location filter if provided
        if ($request->has('locations')) {
            $locations = $request->locations;
            if (!empty($locations)) {
                $query->whereIn('asset_location_id', $locations);
            }
        }

        // Apply sub-location filter if provided
        if ($request->has('sub_locations')) {
            $subLocations = $request->sub_locations;
            if (!empty($subLocations)) {
                $query->whereIn('sub_location_id', $subLocations);
            }
        }

        $assets = $query->latest()->get();
        return response()->json($assets);
        $locations = AssetLocation::with('subLocations')->get();
        return Inertia::render('Assets/Index', [
            'assets' => $assets,
            "locations" => $locations
        ]);
    }

    public function storeCategory(Request $request){
        try {
            $request->validate([
                'name' => 'required',
            ]);
            
            $category = AssetCategory::create([
                'name' => $request->name
            ]);
            
            return response()->json($category, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function storeAssetLocation(Request $request){
        try {
            $request->validate([
                'name' => 'required',
            ]);
            $location = AssetLocation::create([
                'name' => $request->name
            ]);
            return response()->json($location, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function storeFundSource(Request $request){
        try {
            $request->validate([
                'name' => 'required',
            ]);
            $fundSource = FundSource::create([
                'name' => $request->name
            ]);
            return response()->json($fundSource, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function storeRegion(Request $request){
        try {
            $request->validate([
                'name' => 'required|unique:regions',
            ],[
                'name.unique' => $request->name . " already exists",
            ]);
            
            $region = Region::create([
                'name' => $request->name
            ]);
            
            return response()->json($region->name, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function storeSourceFund(Request $request){
        try {
            $request->validate([
                'name' => 'required',
            ]);
            
            $source = fundSource::create([
                'name' => $request->name
            ]);
            
            return response()->json($source, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function create()
    {
        $locations = AssetLocation::all();
        $categories = AssetCategory::all();
        $fundSources = fundSource::get();
        $regions = Region::get();
        $types = AssetType::all();
        $users = User::select('id','name','email')->get();
        $assignees = Assignee::select('id','name')->orderBy('name')->get();
        return Inertia::render('Assets/Create', [
            'locations' => $locations,
            'categories' => $categories,
            'fundSources' => $fundSources,
            'regions' => $regions,
            'types' => $types,
            'users' => $users,
            'assignees' => $assignees,
        ]);
    }

        /**
     * Import assets from an uploaded Excel file.
     */
    public function import(Request $request)
    {        
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls'
            ]);
            // Queue the import job
            Excel::queue(new AssetsImport, $request->file('file'));
            return response()->json('Import started. You will be notified when complete.');
        } catch (\Throwable $e) {
            return response($e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function() use ($request){
                // Validate the request
                $validated = $request->validate([
                    'asset_tag' => 'required|string|max:255',
                    'asset_category_id' => 'required',
                'region_id' => 'required',
                'fund_source_id' => 'required',
                'asset_location_id' => 'required',
                'sub_location_id' => 'required',
                'serial_number' => 'required|string|max:255|unique:assets,serial_number,' . $request->id,
                'item_description' => 'nullable|string',
                'person_assigned' => 'nullable|string',
                'acquisition_date' => 'required|date',
                'status' => 'required|string|in:active,in_use,maintenance,retired,disposed',
                'original_value' => 'required|numeric|min:0',
                'has_warranty' => 'required|in:0,1,true,false',
                'has_documents' => 'required|in:0,1,true,false',
                'asset_warranty_start' => 'nullable|date|required_if:has_warranty,1',
                'asset_warranty_end' => 'nullable|date|required_if:has_warranty,1|after_or_equal:asset_warranty_start',
                // New optional fields
                'tag_no' => 'nullable|string|unique:assets,tag_no',
                'name' => 'nullable|string|max:255',
                'type_id' => 'nullable|exists:asset_types,id',
                'assigned_to' => 'nullable|exists:users,id',
                'assignee_id' => 'nullable|exists:assignees,id',
                'assignee_name' => 'nullable|string|max:255',
                'assignee_email' => 'nullable|email',
                'assignee_phone' => 'nullable|string|max:50',
                'assignee_department' => 'nullable|string|max:100',
                'serial_no' => 'nullable|string|max:255',
                'purchase_date' => 'nullable|date',
                'cost' => 'nullable|numeric',
                'supplier' => 'nullable|string|max:255',
                'warranty_months' => 'nullable|integer|min:0',
                'maintenance_interval_months' => 'nullable|integer|min:0',
                'last_maintenance_at' => 'nullable|date',
                'documents' => 'array|required_if:has_documents,1',
                'documents.*.type' => 'required_if:has_documents,1|string',
                'documents.*.file' => 'nullable'
            ]);
            
            // Convert string boolean values to actual booleans
            $hasWarranty = filter_var($request->has_warranty, FILTER_VALIDATE_BOOLEAN);
            $hasDocuments = filter_var($request->has_documents, FILTER_VALIDATE_BOOLEAN);
            
            // Create asset data array with the correct IDs
            // Use provided assignee or create a new one if details provided
            $assigneeId = null;
            // If a valid assignee_id provided and exists, use it
            if ($request->filled('assignee_id')) {
                $assigneeId = optional(\App\Models\Assignee::find($request->assignee_id))->id;
            }
            if (!$assigneeId && ($request->assignee_name)) {
                $assignee = Assignee::create([
                    'name' => $request->assignee_name,
                    'email' => $request->assignee_email,
                    'phone' => $request->assignee_phone,
                    'department' => $request->assignee_department,
                ]);
                $assigneeId = $assignee->id;
            }

            $assetData = [
                'tag_no' => $request->tag_no,
                'name' => $request->name,
                'asset_tag' => $request->asset_tag,
                'asset_category_id' => $request->asset_category_id,
                'type_id' => $request->type_id,
                'serial_number' => $request->serial_number,
                'serial_no' => $request->serial_no,
                'item_description' => $request->item_description,
                'person_assigned' => $request->person_assigned
                    ?: optional(User::find($request->assigned_to))->name
                    ?: optional(Assignee::find($assigneeId))->name,
                'asset_location_id' => $request->asset_location_id,
                'sub_location_id' => $request->sub_location_id,
                'assigned_to' => $request->assigned_to,
                'assignee_id' => $assigneeId,
                'fund_source_id' => $request->fund_source_id,
                'region_id' => $request->region_id,
                'acquisition_date' => $request->acquisition_date,
                'purchase_date' => $request->purchase_date,
                'cost' => $request->cost,
                'supplier' => $request->supplier,
                'status' => $request->status,
                'original_value' => $request->original_value,
                'has_warranty' => $hasWarranty,
                'has_documents' => $hasDocuments,
                'asset_warranty_start' => $hasWarranty ? $request->asset_warranty_start : null,
                'asset_warranty_end' => $hasWarranty ? $request->asset_warranty_end : null,
                'warranty_months' => $request->warranty_months,
                'maintenance_interval_months' => $request->maintenance_interval_months,
                'last_maintenance_at' => $request->last_maintenance_at,
            ];
            
            // Create or update the asset
            $asset = Asset::updateOrCreate(
                ['id' => $request->id],
                $assetData
            );

            // If this is a new asset (not an update), create automatic approvals
            if (!$request->id) {
                $asset->createAutomaticApprovals();
            }
            
            // Handle document attachments if has_documents is true
            if ($hasDocuments && !empty($request->documents)) {
                foreach ($request->documents as $document) {
                    if (!empty($document['type']) && !empty($document['file'])) {
                        // Handle file upload
                        if (is_object($document['file']) && method_exists($document['file'], 'getClientOriginalName')) {
                            $fileName = time() . '_' . $document['file']->getClientOriginalName();
                            
                            // Create documents directory if it doesn't exist
                            $documentPath = public_path('documents');
                            if (!file_exists($documentPath)) {
                                mkdir($documentPath, 0755, true);
                            }
                            
                            // Move the file to the public directory
                            $document['file']->move($documentPath, $fileName);
                            $filePath = 'documents/' . $fileName;
                            
                            // Create attachment record
                            $asset->attachments()->create([
                                'type' => $document['type'],
                                'file' => $filePath
                            ]);
                        }
                    }
                }
            }
    
            return response()->json([
                'message' => $request->id ? 'Asset updated successfully' : 'Asset created successfully',
                'asset' => $asset
            ], 200);
        });
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function edit(Asset $asset)
    {
        // Use relationship names that the frontend expects (location instead of assetLocation)
        $asset = Asset::with('category:id,name', 'assetLocation:id,name', 'subLocation:id,name', 'fundSource','type:id,name', 'region', 'assignee')
            ->findOrFail($asset->id);
        $locations = AssetLocation::orderBy('name')->get();
        $categories = AssetCategory::orderBy('name')->get();
        $fundSources = fundSource::orderBy('name')->get();
        $regions = Region::orderBy('name')->get();
        $types = AssetType::orderBy('name')->get();
        $assignees = Assignee::select('id','name')->orderBy('name')->get();
        return Inertia::render('Assets/Edit', [
            'asset' => $asset,
            'locations' => $locations,
            'categories' => $categories,
            'fundSources' => $fundSources,
            'regions' => $regions,
            'types' => $types,
            'assignees' => $assignees,
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        try {
            $validated = $request->validate([
                'asset_tag' => 'required|string|max:255',
                'asset_category_id' => 'required|exists:asset_categories,id',
                'type_id' => 'nullable|exists:asset_types,id',
                'tag_no' => 'nullable|string|max:255',
                'name' => 'nullable|string|max:255',
                'serial_number' => 'required|string|max:255|unique:assets,serial_number,' . $asset->id,
                'serial_no' => 'nullable|string|max:255',
                'item_description' => 'nullable|string',
                'person_assigned' => 'nullable|string',
                'asset_location_id' => 'required|exists:asset_locations,id',
                'sub_location_id' => 'required|exists:sub_locations,id',
                'region_id' => 'required|exists:regions,id',
                'fund_source_id' => 'required|exists:fund_sources,id',
                'assigned_to' => 'nullable|exists:users,id',
                'assignee_id' => 'nullable|exists:assignees,id',
                'acquisition_date' => 'required|date',
                'status' => 'required|string|in:active,in_use,maintenance,retired,disposed',
                'original_value' => 'required|numeric|min:0',
            ]);

            $asset->update($validated);

            return response()->json('Updated', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return response()->json('Deleted', 200);
    }
    
    /**
     * Display the asset locations index page.
     */
    public function locationIndex()
    {
        $locations = AssetLocation::all();
        return Inertia::render('Assets/Locations/Index', [
            'locations' => $locations
        ]);
    }
    
    /**
     * Display the asset sub-locations index page.
     */
    public function subLocationIndex()
    {
        $subLocations = SubLocation::with('location')->get();
        $locations = AssetLocation::all();
        return Inertia::render('Assets/SubLocations/Index', [
            'subLocations' => $subLocations,
            'locations' => $locations
        ]);
    }

    public function getSubLocations($locationId)
    {
        $subLocations = SubLocation::where('asset_location_id', $locationId)->get();
        return response()->json($subLocations);
    }

    // transfer submission with approval
    public function transferAsset(Request $request, Asset $asset)
    {
        try {
            // Check transfer_initiate permission
            if (!auth()->user()->can('transfer_initiate')) {
                return response()->json('You do not have permission to initiate transfers', 403);
            }

            $validated = $request->validate([
                'custodian' => 'required|string',
                'assignment_notes' => 'nullable|string'
            ]);

            // Check if asset is available for transfer
            if ($asset->status !== Asset::STATUS_IN_USE) {
                return response()->json('Asset is not available for transfer', 400);
            }

            // Check if asset is already submitted for approval
            if ($asset->submitted_for_approval) {
                return response()->json('Asset is already submitted for approval', 400);
            }

            DB::transaction(function () use ($asset, $validated) {
                // Set asset status to in transfer process
                $oldStatus = $asset->status;
                $asset->update([
                    'status' => Asset::STATUS_IN_TRANSFER_PROCESS,
                    'submitted_for_approval' => true,
                    'submitted_at' => now(),
                    'submitted_by' => auth()->id()
                ]);

                // Create history record for transfer initiation
                $asset->createHistoryRecord(
                    'transfer_initiated',
                    'transfer',
                    ['status' => $oldStatus, 'person_assigned' => $asset->person_assigned],
                    ['status' => Asset::STATUS_IN_TRANSFER_PROCESS, 'new_custodian' => $validated['custodian']],
                    "Transfer initiated: {$asset->person_assigned} → {$validated['custodian']}"
                );

                // Clear any existing approvals for this asset
                $asset->approvals()->delete();

                // Create transfer approval steps: Single review step
                $asset->createApprovalSteps([
                    [
                        'role_id' => 1, // Use a general role ID that all users can have
                        'action' => 'review',
                        'sequence' => 1,
                        'notes' => "Transfer request: {$asset->person_assigned} → {$validated['custodian']}"
                    ]
                ]);

                // Store transfer data in the approval
                $approval = $asset->approvals()->first();
                $approval->update([
                    'transfer_data' => json_encode([
                        'old_custodian' => $asset->person_assigned,
                        'new_custodian' => $validated['custodian'],
                        'assignment_notes' => $validated['assignment_notes'] ?? null
                    ])
                ]);
            });

            return response()->json([
                'message' => 'Asset transfer request submitted for approval',
                'asset' => $asset->fresh()
            ], 200);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Approve transfer
     */
    public function approveTransfer(Request $request, Asset $asset)
    {
        try {
            $validated = $request->validate([
                'approval_id' => 'required|exists:asset_approvals,id',
                'action' => 'required|in:review,approve,reject',
                'notes' => 'nullable|string'
            ]);

            $approval = AssetApproval::findOrFail($validated['approval_id']);
            
            if ($approval->action !== 'review') {
                return response()->json('Invalid approval type', 400);
            }

            if ($validated['action'] === 'review') {
                // Check transfer_review permission
                if (!auth()->user()->can('transfer_review')) {
                    return response()->json('You do not have permission to review transfers', 403);
                }

                // Handle review step
                if ($approval->status !== 'pending') {
                    return response()->json('Current step is not a pending review step', 400);
                }

                $approval->update([
                    'status' => 'reviewed',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'notes' => $validated['notes'] ?? null
                ]);

                // Create history record
                $asset->createHistoryRecord(
                    'transfer_reviewed',
                    'transfer',
                    null,
                    null,
                    $validated['notes'] ?? 'Transfer reviewed',
                    $approval->id
                );

                return response()->json([
                    'message' => 'Transfer reviewed successfully, waiting for final approval',
                    'asset' => $asset->fresh()
                ], 200);

            } elseif ($validated['action'] === 'approve') {
                // Check transfer_approve permission
                if (!auth()->user()->can('transfer_approve')) {
                    return response()->json('You do not have permission to approve transfers', 403);
                }

                // Handle approve step - execute the transfer
                if ($approval->status !== 'reviewed') {
                    return response()->json('Current step is not a reviewed step ready for approval', 400);
                }

                // Get transfer data from the approval
                if (!$approval->transfer_data) {
                    return response()->json('Transfer data not found', 400);
                }

                $transferData = json_decode($approval->transfer_data, true);
                $oldCustodian = $asset->person_assigned;
                $oldStatus = $asset->status;
                
                // Update asset
                $asset->update([
                    'person_assigned' => $transferData['new_custodian'],
                    'status' => Asset::STATUS_IN_USE,
                    'submitted_for_approval' => false
                ]);

                // Create custody history
                $custodyHistory = \App\Models\CustodyHistory::create([
                    'asset_id' => $asset->id,
                    'custodian' => $transferData['new_custodian'],
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'assignment_notes' => $transferData['assignment_notes'] ?? null,
                    'status' => 'assigned',
                    'status_notes' => 'Transferred from ' . $transferData['old_custodian'] . ' to ' . $transferData['new_custodian'],
                ]);

                // Update approval status
                $approval->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes' => $validated['notes'] ?? null
                ]);

                // Create history record
                $asset->createHistoryRecord(
                    'transfer_approved',
                    'transfer',
                    [
                        'status' => $oldStatus,
                        'person_assigned' => $oldCustodian
                    ],
                    [
                        'status' => Asset::STATUS_IN_USE,
                        'person_assigned' => $transferData['new_custodian']
                    ],
                    $validated['notes'] ?? 'Transfer approved and executed',
                    $approval->id
                );

                return response()->json([
                    'message' => 'Transfer approved and executed successfully',
                    'asset' => $asset->fresh(),
                    'custody_history' => $custodyHistory
                ], 200);

            } else {
                // Check transfer_reject permission
                if (!auth()->user()->can('transfer_reject')) {
                    return response()->json('You do not have permission to reject transfers', 403);
                }

                // Handle reject step
                if ($approval->status !== 'reviewed') {
                    return response()->json('Current step is not a reviewed step ready for approval', 400);
                }

                // Update approval status
                $approval->update([
                    'status' => 'rejected',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes' => $validated['notes'] ?? null
                ]);

                // Set asset status back to IN_USE (original status before transfer)
                $oldStatus = $asset->status;
                $asset->update([
                    'status' => Asset::STATUS_IN_USE,
                    'submitted_for_approval' => false
                ]);

                // Create history record
                $asset->createHistoryRecord(
                    'transfer_rejected',
                    'transfer',
                    ['status' => $oldStatus],
                    ['status' => Asset::STATUS_IN_USE],
                    $validated['notes'] ?? 'Transfer rejected',
                    $approval->id
                );

                return response()->json([
                    'message' => 'Transfer rejected',
                    'asset' => $asset->fresh()
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
    /**
     * Retire asset with approval process
     */
    public function retireAsset(Request $request, Asset $asset)
    {
        try {
            // Check retirement_initiate permission
            if (!auth()->user()->can('retirement_initiate')) {
                return response()->json('You do not have permission to initiate retirements', 403);
            }

            $validated = $request->validate([
                'retirement_reason' => 'required|string|max:500',
                'retirement_date' => 'required|date'
            ]);

            // Check if asset is available for retirement
            if ($asset->status !== Asset::STATUS_IN_USE && $asset->status !== Asset::STATUS_ACTIVE) {
                return response()->json('Asset is not available for retirement', 400);
            }

            // Check if asset is already submitted for approval
            if ($asset->submitted_for_approval) {
                return response()->json('Asset is already submitted for approval', 400);
            }

            DB::transaction(function () use ($asset, $validated) {
                // Set asset status to pending approval for retirement
                $oldStatus = $asset->status;
                $asset->update([
                    'status' => Asset::STATUS_PENDING_APPROVAL,
                    'submitted_for_approval' => true,
                    'submitted_at' => now(),
                    'submitted_by' => auth()->id()
                ]);

                // Create history record for retirement initiation
                $asset->createHistoryRecord(
                    'retirement_initiated',
                    'retirement',
                    ['status' => $oldStatus],
                    ['status' => Asset::STATUS_PENDING_APPROVAL],
                    "Retirement initiated: {$validated['retirement_reason']}"
                );

                // Clear any existing approvals for this asset
                $asset->approvals()->delete();

                // Create retirement approval steps: Single review step
                $asset->createApprovalSteps([
                    [
                        'role_id' => 1, // Use a general role ID that all users can have
                        'action' => 'review',
                        'sequence' => 1,
                        'notes' => "Retirement request for asset: {$asset->asset_tag}"
                    ]
                ]);

                // Store retirement data in the approval
                $approval = $asset->approvals()->first();
                $approval->update([
                    'transfer_data' => json_encode([
                        'retirement_reason' => $validated['retirement_reason'],
                        'retirement_date' => $validated['retirement_date'],
                        'previous_status' => $oldStatus
                    ])
                ]);
            });

            return response()->json([
                'message' => 'Asset retirement request submitted for approval',
                'asset' => $asset->fresh()
            ], 200);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Approve retirement
     */
    public function approveRetirement(Request $request, Asset $asset)
    {
        try {
            $validated = $request->validate([
                'approval_id' => 'required|exists:asset_approvals,id',
                'action' => 'required|in:review,approve,reject',
                'notes' => 'nullable|string'
            ]);

            $approval = AssetApproval::findOrFail($validated['approval_id']);
            
            if ($approval->action !== 'review') {
                return response()->json('Invalid approval type', 400);
            }

            if ($validated['action'] === 'review') {
                // Check retirement_review permission
                if (!auth()->user()->can('retirement_review')) {
                    return response()->json('You do not have permission to review retirements', 403);
                }

                // Handle review step
                if ($approval->status !== 'pending') {
                    return response()->json('Current step is not a pending review step', 400);
                }

                $approval->update([
                    'status' => 'reviewed',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'notes' => $validated['notes'] ?? null
                ]);

                // Create history record
                $asset->createHistoryRecord(
                    'retirement_reviewed',
                    'retirement',
                    null,
                    null,
                    $validated['notes'] ?? 'Retirement reviewed',
                    $approval->id
                );

                return response()->json([
                    'message' => 'Retirement reviewed successfully, waiting for final approval',
                    'asset' => $asset->fresh()
                ], 200);

            } elseif ($validated['action'] === 'approve') {
                // Check retirement_approve permission
                if (!auth()->user()->can('retirement_approve')) {
                    return response()->json('You do not have permission to approve retirements', 403);
                }

                // Handle approve step - execute the retirement
                if ($approval->status !== 'reviewed') {
                    return response()->json('Current step is not a reviewed step ready for approval', 400);
                }

                // Get retirement data from the approval
                if (!$approval->transfer_data) {
                    return response()->json('Retirement data not found', 400);
                }

                $retirementData = json_decode($approval->transfer_data, true);
                $oldStatus = $asset->status;
                
                // Update asset status to retired
                $asset->update([
                    'status' => Asset::STATUS_RETIRED,
                    'submitted_for_approval' => false
                ]);

                // Update approval status
                $approval->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes' => $validated['notes'] ?? null
                ]);

                // Create history record
                $asset->createHistoryRecord(
                    'retirement_approved',
                    'retirement',
                    ['status' => $oldStatus],
                    ['status' => Asset::STATUS_RETIRED],
                    $validated['notes'] ?? 'Retirement approved and executed',
                    $approval->id
                );

                return response()->json([
                    'message' => 'Asset retirement approved and executed successfully',
                    'asset' => $asset->fresh()
                ], 200);

            } else {
                // Check retirement_reject permission
                if (!auth()->user()->can('retirement_reject')) {
                    return response()->json('You do not have permission to reject retirements', 403);
                }

                // Handle reject step
                if ($approval->status !== 'reviewed') {
                    return response()->json('Current step is not a reviewed step ready for approval', 400);
                }

                // Update approval status
                $approval->update([
                    'status' => 'rejected',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes' => $validated['notes'] ?? null
                ]);

                // Get retirement data to restore previous status
                $retirementData = json_decode($approval->transfer_data, true);
                $previousStatus = $retirementData['previous_status'] ?? Asset::STATUS_IN_USE;
                $oldStatus = $asset->status;

                // Set asset status back to previous status
                $asset->update([
                    'status' => $previousStatus,
                    'submitted_for_approval' => false
                ]);

                // Create history record
                $asset->createHistoryRecord(
                    'retirement_rejected',
                    'retirement',
                    ['status' => $oldStatus],
                    ['status' => $previousStatus],
                    $validated['notes'] ?? 'Retirement rejected',
                    $approval->id
                );

                return response()->json([
                    'message' => 'Retirement rejected',
                    'asset' => $asset->fresh()
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Store a new sub-location.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeSubLocation(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'asset_location_id' => 'required|exists:asset_locations,id'
            ]);
            
            $subLocation = SubLocation::create([
                'name' => $request->name,
                'asset_location_id' => $request->asset_location_id
            ]);
            
            return response()->json($subLocation, 201);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Submit asset for approval
     */
    public function submitForApproval(Request $request, Asset $asset)
    {
        try {
            // Check if asset is already submitted for approval
            if ($asset->isPendingApproval()) {
                return response()->json('Asset is already pending approval', 400);
            }

            // Submit asset for approval
            $asset->submitForApproval();

            return response()->json([
                'message' => 'Asset submitted for approval successfully',
                'asset' => $asset->fresh()
            ], 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Approve asset
     */
    public function approveAsset(Request $request, Asset $asset)
    {
        try {
            logger()->info('Asset approval request', [
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'action' => $request->action,
                'user_permissions' => auth()->user()->getAllPermissions()->pluck('name')
            ]);

            // Check if current user can approve this asset
            if (!$asset->canBeApprovedByCurrentUser()) {
                logger()->warning('User not authorized to approve asset', [
                    'asset_id' => $asset->id,
                    'user_id' => auth()->id()
                ]);
                return response()->json('You are not authorized to approve this asset', 403);
            }

            $validated = $request->validate([
                'notes' => 'nullable|string',
                'action' => 'required|in:review,approve,reject'
            ]);

            $nextStep = $asset->getNextApprovalStep();
            if (!$nextStep) {
                logger()->warning('No pending approval step found', [
                    'asset_id' => $asset->id,
                    'approval_steps' => $asset->getAllApprovalSteps()->toArray()
                ]);
                return response()->json('No pending approval step found', 400);
            }

            logger()->info('Processing approval step', [
                'step_id' => $nextStep->id,
                'step_status' => $nextStep->status,
                'step_action' => $nextStep->action,
                'requested_action' => $validated['action']
            ]);

            if ($validated['action'] === 'review') {
                // Check asset_review permission
                if (!auth()->user()->can('asset_review')) {
                    return response()->json('You do not have permission to review assets', 403);
                }

                // Handle review step
                if ($nextStep->action !== 'review' || $nextStep->status !== 'pending') {
                    return response()->json('Current step is not a pending review step', 400);
                }

                $nextStep->update([
                    'status' => 'reviewed',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'notes' => $validated['notes'] ?? null,
                    'updated_by' => auth()->id()
                ]);

                // Create history record
                $asset->createHistoryRecord(
                    'reviewed',
                    'approval',
                    ['status' => 'pending'],
                    ['status' => 'reviewed'],
                    $validated['notes'] ?? 'Asset reviewed',
                    $nextStep->id
                );

                return response()->json([
                    'message' => 'Asset reviewed successfully, waiting for final approval',
                    'asset' => $asset->fresh()
                ], 200);

            } elseif ($validated['action'] === 'approve') {
                // Check asset_approve permission
                if (!auth()->user()->can('asset_approve')) {
                    return response()->json('You do not have permission to approve assets', 403);
                }

                // Handle approve step
                if ($nextStep->action !== 'review' || $nextStep->status !== 'reviewed') {
                    return response()->json('Current step is not a reviewed step ready for approval', 400);
                }

                $oldStatus = $asset->status;
                
                $nextStep->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes' => $validated['notes'] ?? null,
                    'updated_by' => auth()->id()
                ]);

                // Check if this is the final approval step
                $remainingSteps = $asset->approvals()->where('status', 'pending')->count();
                
                if ($remainingSteps === 0) {
                    // Asset is fully approved, set status to IN_USE
                    $asset->update([
                        'status' => Asset::STATUS_IN_USE,
                        'submitted_for_approval' => false
                    ]);

                    // Create history record
                    $asset->createHistoryRecord(
                        'approved',
                        'approval',
                        ['status' => $oldStatus],
                        ['status' => Asset::STATUS_IN_USE],
                        $validated['notes'] ?? 'Asset approved',
                        $nextStep->id
                    );
                    
                    return response()->json([
                        'message' => 'Asset fully approved and ready for use',
                        'asset' => $asset->fresh()
                    ], 200);
                } else {
                    // Create history record for step approval
                    $asset->createHistoryRecord(
                        'step_approved',
                        'approval',
                        ['status' => 'reviewed'],
                        ['status' => 'approved'],
                        $validated['notes'] ?? 'Approval step completed',
                        $nextStep->id
                    );
                    
                    return response()->json([
                        'message' => 'Approval step completed, waiting for next step',
                        'asset' => $asset->fresh()
                    ], 200);
                }

            } else {
                // Check asset_reject permission
                if (!auth()->user()->can('asset_reject')) {
                    return response()->json('You do not have permission to reject assets', 403);
                }

                // Handle reject step
                if ($nextStep->action !== 'review' || $nextStep->status !== 'reviewed') {
                    return response()->json('Current step is not a reviewed step ready for approval', 400);
                }

                $oldStatus = $asset->status;
                
                $nextStep->update([
                    'status' => 'rejected',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes' => $validated['notes'] ?? null,
                    'updated_by' => auth()->id()
                ]);

                // Asset is rejected, set status back to ACTIVE
                $asset->update([
                    'status' => Asset::STATUS_ACTIVE,
                    'submitted_for_approval' => false
                ]);

                // Create history record
                $asset->createHistoryRecord(
                    'rejected',
                    'approval',
                    ['status' => $oldStatus],
                    ['status' => Asset::STATUS_ACTIVE],
                    $validated['notes'] ?? 'Asset rejected',
                    $nextStep->id
                );

                return response()->json([
                    'message' => 'Asset rejected',
                    'asset' => $asset->fresh()
                ], 200);
            }
        } catch (\Throwable $th) {
            logger()->error('Asset approval error', [
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Debug approval workflow for an asset
     */
    public function debugApprovalWorkflow(Asset $asset)
    {
        try {
            $user = auth()->user();
            $approvalSteps = $asset->getAllApprovalSteps();
            $nextStep = $asset->getNextApprovalStep();
            
            $debug = [
                'asset' => [
                    'id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'status' => $asset->status,
                    'submitted_for_approval' => $asset->submitted_for_approval,
                    'submitted_at' => $asset->submitted_at,
                    'submitted_by' => $asset->submitted_by
                ],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'roles' => $user->roles->pluck('name'),
                    'permissions' => $user->getAllPermissions()->pluck('name')
                ],
                'approval_steps' => $approvalSteps->map(function($step) {
                    return [
                        'id' => $step->id,
                        'role' => $step->role->name,
                        'action' => $step->action,
                        'sequence' => $step->sequence,
                        'status' => $step->status,
                        'reviewed_by' => $step->reviewer?->name,
                        'reviewed_at' => $step->reviewed_at,
                        'approved_by' => $step->approver?->name,
                        'approved_at' => $step->approved_at
                    ];
                }),
                'next_step' => $nextStep ? [
                    'id' => $nextStep->id,
                    'role' => $nextStep->role->name,
                    'action' => $nextStep->action,
                    'sequence' => $nextStep->sequence,
                    'status' => $nextStep->status
                ] : null,
                'can_review' => $asset->canBeReviewedByCurrentUser(),
                'can_approve' => $asset->canBeApprovedByCurrentUser(),
                'can_approve_reject' => $asset->canBeApprovedRejectedByCurrentUser()
            ];

            return response()->json($debug);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * Get pending approvals for current user
     */
    public function getPendingApprovals(Request $request)
    {
        try {
            $user = auth()->user();
            $pendingAssets = Asset::whereHas('approvals', function($query) use ($user) {
                $query->where('status', 'pending')
                      ->whereHas('role', function($roleQuery) use ($user) {
                          $roleQuery->whereIn('name', $user->roles->pluck('name'));
                      });
            })->with(['approvals' => function($query) use ($user) {
                $query->where('status', 'pending')
                      ->whereHas('role', function($roleQuery) use ($user) {
                          $roleQuery->whereIn('name', $user->roles->pluck('name'));
                      });
            }, 'category', 'location', 'subLocation', 'submittedBy'])
            ->get();

            return response()->json([
                'pending_assets' => $pendingAssets
            ], 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Get approval history for an asset
     */
    public function getApprovalHistory(Asset $asset)
    {
        try {
            $approvalHistory = $asset->approvals()
                ->with(['approver', 'role'])
                ->orderBy('sequence')
                ->get();

            return response()->json([
                'approval_history' => $approvalHistory
            ], 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Get complete asset history
     */
    public function getAssetHistory(Request $request, Asset $asset)
    {
        try {
            $query = $asset->assetHistory()
                ->with(['performer', 'approval'])
                ->orderBy('performed_at', 'desc');

            // Apply filters
            if ($request->filled('actionType')) {
                $query->where('action_type', $request->actionType);
            }

            if ($request->filled('performedBy')) {
                $query->where('performed_by', $request->performedBy);
            }

            // Date range filter
            if ($request->filled('dateRange')) {
                $now = now();
                switch ($request->dateRange) {
                    case 'today':
                        $query->whereDate('performed_at', $now->toDateString());
                        break;
                    case 'week':
                        $query->whereBetween('performed_at', [$now->startOfWeek(), $now->endOfWeek()]);
                        break;
                    case 'month':
                        $query->whereBetween('performed_at', [$now->startOfMonth(), $now->endOfMonth()]);
                        break;
                    case 'quarter':
                        $query->whereBetween('performed_at', [$now->startOfQuarter(), $now->endOfQuarter()]);
                        break;
                    case 'year':
                        $query->whereBetween('performed_at', [$now->startOfYear(), $now->endOfYear()]);
                        break;
                }
            }

            $history = $query->paginate(10);

            // Get users for filter
            $users = User::whereIn('id', $asset->assetHistory()->distinct('performed_by')->pluck('performed_by'))->get();

            return Inertia::render('Assets/History', [
                'asset' => $asset->load(['category', 'location', 'subLocation']),
                'history' => $history,
                'users' => $users,
                'filters' => $request->only('actionType', 'dateRange', 'performedBy'),
            ]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Get all asset history (general history page)
     */
    public function getAllAssetHistory(Request $request)
    {
        try {
            $query = AssetHistory::with(['asset', 'performer', 'approval'])
                ->orderBy('performed_at', 'desc');

            // Apply filters
            if ($request->filled('asset_id')) {
                $query->where('asset_id', $request->asset_id);
            }

            if ($request->filled('action_type')) {
                $query->where('action_type', $request->action_type);
            }

            if ($request->filled('performer')) {
                $query->whereHas('performer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->performer . '%');
                });
            }

            if ($request->filled('date_from')) {
                $query->where('performed_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('performed_at', '<=', $request->date_to . ' 23:59:59');
            }

            $perPage = $request->get('per_page', 15);
            $history = $query->paginate($perPage);

            return Inertia::render('Assets/History/Index', [
                'history' => $history,
                'filters' => $request->only(['asset_id', 'action_type', 'performer', 'date_from', 'date_to']),
                'assets' => Asset::select('id', 'asset_tag', 'serial_number')->get(),
            ]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Display the approvals index page
     */
    public function approvalsIndex(Request $request)
    {
        $query = AssetApproval::with(['approvable', 'role', 'approver', 'reviewer', 'creator'])
            ->where('approvable_type', Asset::class);

        // Apply filters
        if ($request->filled('search')) {
            $query->whereHas('approvable', function($q) use ($request) {
                $q->where('asset_tag', 'like', '%' . $request->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $request->search . '%')
                  ->orWhere('item_description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }



        $approvals = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get counts
        $pendingCount = AssetApproval::where('approvable_type', Asset::class)
            ->where('status', 'pending')->count();
        $approvedCount = AssetApproval::where('approvable_type', Asset::class)
            ->where('status', 'approved')->count();

        return Inertia::render('Assets/Approvals', [
            'approvals' => $approvals,
            'filters' => $request->only('search', 'status'),
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
        ]);
    }
}
