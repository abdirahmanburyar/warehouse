<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetItem;
use App\Models\SubLocation;
use App\Models\AssetLocation;
use App\Models\Region;
use App\Models\User;
use App\Models\Assignee;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\AssetHistory;
use App\Models\FundSource;
use App\Models\AssetDocument;

use App\Models\AssetMaintenance;
use App\Models\AssetDepreciation;
use App\Http\Resources\AssetResource;
use App\Http\Resources\AssetItemResource;
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
        $assetItems = AssetItem::query();

        if($request->filled('search')){
            $assetItems->where(function($query) use ($request) {
                $query->whereLike('asset_tag', '%'.$request->search.'%')   
                    ->orWhereLike('asset_name', '%'.$request->search.'%')
                ->orWhereLike('serial_number', '%'.$request->search.'%')
                    ->orWhereHas('asset.fundSource', function($q) use ($request){
                        $q->where('name', 'like', '%'.$request->search.'%');
                    });
                });
        }

        if($request->filled('region_id')){
            $assetItems->whereHas('asset', function($query) use ($request) {
                $query->where('region_id', $request->region_id);
            });
        }

        if($request->filled('location_id')){
            $assetItems->whereHas('asset', function($query) use ($request) {
                $query->where('asset_location_id', $request->location_id);
            });
        }

        if($request->filled('sub_location_ids') && is_array($request->sub_location_ids)){
            $assetItems->whereHas('asset', function($query) use ($request) {
                $query->whereIn('sub_location_id', $request->sub_location_ids);
            });
        } elseif($request->filled('sub_location_id')){
            $assetItems->whereHas('asset', function($query) use ($request) {
                $query->where('sub_location_id', $request->sub_location_id);
            });
        }

        if($request->filled('fund_source_id')){
            $assetItems->whereHas('asset', function($query) use ($request) {
                $query->where('fund_source_id', $request->fund_source_id);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $assetItems->where('status', $request->status);
        }

        // New filters
        if ($request->filled('category_id')) {
            $assetItems->where('asset_category_id', $request->category_id);
        }
        if ($request->filled('type_id')) {
            $assetItems->where('asset_type_id', $request->type_id);
        }
        if ($request->filled('assignee_id')) {
            $assetItems->where('assignee_id', $request->assignee_id);
        }
        if ($request->filled('acquisition_from') || $request->filled('acquisition_to')) {
            $from = $request->input('acquisition_from');
            $to = $request->input('acquisition_to');
            if ($from && $to) {
                $assetItems->whereHas('asset', function($query) use ($from, $to) {
                    $query->whereBetween('acquisition_date', [$from, $to]);
                });
            } elseif ($from) {
                $assetItems->whereHas('asset', function($query) use ($from) {
                    $query->whereDate('acquisition_date', '>=', $from);
                });
            } elseif ($to) {
                $assetItems->whereHas('asset', function($query) use ($to) {
                    $query->whereDate('acquisition_date', '<=', $to);
                });
            }
        }
        if ($request->filled('created_from') || $request->filled('created_to')) {
            $from = $request->input('created_from');
            $to = $request->input('created_to');
            if ($from && $to) {
                $assetItems->whereBetween('created_at', [$from, $to]);
            } elseif ($from) {
                $assetItems->whereDate('created_at', '>=', $from);
            } elseif ($to) {
                $assetItems->whereDate('created_at', '<=', $to);
            }
        }
    
        $assetItems->orderBy('created_at', 'desc');

        $assetItems = $assetItems->with([
            'asset:id,asset_number,acquisition_date,fund_source_id,region_id,asset_location_id,sub_location_id',
            'asset.fundSource:id,name',
            'asset.region:id,name',
            'asset.assetLocation:id,name',
            'asset.subLocation:id,name',
            'category:id,name',
            'type:id,name',
            'assignee:id,name'
        ])
            ->paginate($request->input('per_page', 10), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        $assetItems->setPath(url()->current()); // Force Laravel to use full URLs

        $count = AssetItem::count();

        $locations = AssetLocation::with('subLocations')->get();
        $categories = AssetCategory::select('id','name')->get();
        $types = AssetType::select('id','name','asset_category_id')->get();
        $assignees = Assignee::select('id','name')->get();
        
        return inertia('Assets/Index', [
            'locations' => $locations,
            'assets' => AssetItemResource::collection($assetItems),
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
                        
                        // Create document record
                        $asset->documents()->create([
                            'document_type' => $document['type'],
                            'file_path' => $path,
                            'file_name' => $document['file']->getClientOriginalName(),
                            'mime_type' => $document['file']->getMimeType(),
                            'file_size' => $document['file']->getSize(),
                            'description' => $document['description'] ?? null,
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
            $document = AssetDocument::findOrFail($id);

            // Delete the physical file first (if it exists)
            if ($document->file_path) {
                $fullPath = storage_path('app/public/' . $document->file_path);
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
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
        $assetItem = AssetItem::with([
            'asset:id,asset_number,acquisition_date,fund_source_id,region_id,asset_location_id,sub_location_id',
            'asset.fundSource:id,name',
            'asset.region:id,name',
            'asset.assetLocation:id,name',
            'asset.subLocation:id,name',
            'category:id,name',
            'type:id,name',
            'assignee:id,name',
            'maintenance',
            'depreciation'
        ])->findOrFail($id);

        return inertia('Assets/Show', [
            'assetItem' => $assetItem,
        ]);
    }

    public function getAssets(Request $request)
    {
        $query = AssetItem::query()
            ->with([
                'asset:id,asset_number,acquisition_date,fund_source_id,region_id,asset_location_id,sub_location_id',
                'asset.fundSource:id,name',
                'asset.region:id,name',
                'asset.assetLocation:id,name',
                'asset.subLocation:id,name',
                'category:id,name',
                'type:id,name'
            ]);

        // Apply location filter if provided
        if ($request->has('locations')) {
            $locations = $request->locations;
            if (!empty($locations)) {
                $query->whereHas('asset', function($q) use ($locations) {
                    $q->whereIn('asset_location_id', $locations);
                });
            }
        }

        // Apply sub-location filter if provided
        if ($request->has('sub_locations')) {
            $subLocations = $request->sub_locations;
            if (!empty($subLocations)) {
                $query->whereHas('asset', function($q) use ($subLocations) {
                    $q->whereIn('sub_location_id', $subLocations);
                });
            }
        }

        // Apply region filter if provided
        if ($request->has('regions')) {
            $regions = $request->regions;
            if (!empty($regions)) {
                $query->whereHas('asset', function($q) use ($regions) {
                    $q->whereIn('region_id', $regions);
                });
            }
        }

        // Apply fund source filter if provided
        if ($request->has('fund_sources')) {
            $fundSources = $request->fund_sources;
            if (!empty($fundSources)) {
                $query->whereHas('asset', function($q) use ($fundSources) {
                    $q->whereIn('fund_source_id', $fundSources);
                });
            }
        }

        // Apply category filter if provided
        if ($request->has('categories')) {
            $categories = $request->categories;
            if (!empty($categories)) {
                $query->whereIn('asset_category_id', $categories);
            }
        }

        // Apply type filter if provided
        if ($request->has('types')) {
            $types = $request->types;
            if (!empty($types)) {
                $query->whereIn('asset_type_id', $types);
            }
        }

        // Apply status filter if provided
        if ($request->has('statuses')) {
            $statuses = $request->statuses;
            if (!empty($statuses)) {
                $query->whereIn('status', $statuses);
            }
        }

        // Apply assignee filter if provided
        if ($request->has('assignees')) {
            $assignees = $request->assignees;
            if (!empty($assignees)) {
                $query->whereIn('assignee_id', $assignees);
            }
        }

        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_name', 'like', "%{$search}%")
                  ->orWhere('asset_tag', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhereHas('asset.fundSource', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $assets = $query->get();

        return response()->json($assets);
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
                // Validate the main asset data
                $validatedAsset = $request->validate([
                    'asset_number' => 'nullable|string|unique:assets,asset_number',
                'acquisition_date' => 'required|date',
                    'fund_source_id' => 'required|exists:fund_sources,id',
                    'region_id' => 'required|exists:regions,id',
                    'asset_location_id' => 'required|exists:asset_locations,id',
                    'sub_location_id' => 'required|exists:sub_locations,id',
                ]);

                // Validate asset items array
                $request->validate([
                    'asset_items' => 'required|array|min:1',
                    'asset_items.*.asset_tag' => 'required|string|max:255',
                    'asset_items.*.asset_name' => 'required|string|max:255',
                    'asset_items.*.serial_number' => 'required|string|max:255',
                    'asset_items.*.asset_category_id' => 'required|exists:asset_categories,id',
                    'asset_items.*.asset_type_id' => 'required|exists:asset_types,id',
                    'asset_items.*.assignee_id' => 'nullable|exists:assignees,id',
                    'asset_items.*.original_value' => 'required|numeric|min:0',
                ]);

                // Get asset items data directly
                $assetItemsData = $request->asset_items;
                
                // Generate asset number if not provided
                if (empty($validatedAsset['asset_number'])) {
                    $validatedAsset['asset_number'] = $this->generateAssetNumber();
                }

                // Set default status for the asset
                $validatedAsset['status'] = 'pending_approval';
                $validatedAsset['submitted_by'] = auth()->id();
                $validatedAsset['submitted_at'] = now();

                // Create the main asset record
                $asset = Asset::create($validatedAsset);

                // Create asset items
                $assetItems = [];
                foreach ($assetItemsData as $itemData) {
                    $assetItem = AssetItem::create([
                        'asset_id' => $asset->id,
                        'asset_tag' => $itemData['asset_tag'],
                        'asset_name' => $itemData['asset_name'],
                        'serial_number' => $itemData['serial_number'],
                        'asset_category_id' => $itemData['asset_category_id'],
                        'asset_type_id' => $itemData['asset_type_id'],
                        'assignee_id' => $itemData['assignee_id'],
                        'status' => 'pending_approval', // Default status for new items
                        'original_value' => $itemData['original_value'],
                    ]);

                    $assetItems[] = $assetItem;
                }

                // Create asset history record for creation
                $asset->createHistory([
                    'action' => 'asset_created',
                    'action_type' => 'creation',
                    'notes' => 'Asset created with ' . count($assetItems) . ' items',
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                ]);

                // Create history for each asset item
                foreach ($assetItems as $item) {
                    $item->createHistory([
                        'action' => 'item_created',
                        'action_type' => 'creation',
                        'notes' => 'Asset item created',
                        'performed_by' => auth()->id(),
                        'performed_at' => now(),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Asset created successfully with ' . count($assetItems) . ' items. Pending approval.',
                    'asset' => $asset->load('assetItems'),
                    'redirect_url' => route('assets.index')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create asset: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Generate a unique asset number
     */
    private function generateAssetNumber()
    {
        $prefix = 'AST';
        $year = date('Y');
        $month = date('m');
        
        // Get the last asset number for this month
        $lastAsset = Asset::where('asset_number', 'like', $prefix . $year . $month . '%')
            ->orderBy('asset_number', 'desc')
            ->first();
        
        if ($lastAsset) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastAsset->asset_number, -4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        return $prefix . $year . $month . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
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
                    'asset' => $asset->fresh()
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

            if ($validated['action'] === 'approve') {
                // Check asset_approve permission
                if (!auth()->user()->can('asset_approve')) {
                    return response()->json('You do not have permission to approve assets', 403);
                }

                // Block approval for disallowed statuses
                $disallowedStatuses = [Asset::STATUS_IN_USE, Asset::STATUS_MAINTENANCE, Asset::STATUS_RETIRED];
                if (in_array($asset->status, $disallowedStatuses, true)) {
                    return response()->json('Asset cannot be approved in its current status', 400);
                }

                // Handle approve step (either direct approve step pending or reviewed review step)
                if (!(
                    ($nextStep->action === 'approve' && $nextStep->status === 'pending') ||
                    ($nextStep->action === 'review' && $nextStep->status === 'reviewed')
                )) {
                    return response()->json('Current step is not ready for approval', 400);
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

                // Handle reject step (either direct approve step pending or reviewed review step)
                if (!(
                    ($nextStep->action === 'approve' && $nextStep->status === 'pending') ||
                    ($nextStep->action === 'review' && $nextStep->status === 'reviewed')
                )) {
                    return response()->json('Current step is not ready for rejection', 400);
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
                    'roles' => method_exists($user, 'roles') ? $user->roles->pluck('name') : [],
                    'permissions' => $user->getAllPermissions()->pluck('name')
                ],
                'approval_steps' => $approvalSteps->map(function($step) {
                    return [
                        'id' => $step->id,
                        'role' => optional($step->role)->name,
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
                    'role' => optional($nextStep->role)->name,
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
                $query->where('status', 'pending');
            })->with(['approvals' => function($query) {
                $query->where('status', 'pending');
            }, 'category', 'assetLocation', 'subLocation', 'submittedBy'])
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
                ->with(['approver'])
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
            // Get history from all asset items belonging to this asset
            $assetItemIds = $asset->assetItems()->pluck('id');
            
            $query = AssetHistory::whereIn('asset_item_id', $assetItemIds)
                ->with(['assetItem', 'performer', 'approval'])
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
            $users = User::whereIn('id', AssetHistory::whereIn('asset_item_id', $assetItemIds)->distinct('performed_by')->pluck('performed_by'))->get();

            return Inertia::render('Assets/History', [
                'asset' => $asset->load(['category', 'assetLocation', 'subLocation']),
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
            $query = AssetHistory::with(['assetItem', 'performer', 'approval'])
                ->orderBy('performed_at', 'desc');

            // Apply filters
            if ($request->filled('asset_id')) {
                $query->where('asset_item_id', $request->asset_id);
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
        $query = Asset::with(['assetItems.category', 'assetItems.type', 'assetItems.assignee', 'fundSource', 'region', 'assetLocation', 'subLocation'])
            ->pendingApproval()
            ->whereHas('assetItems', function($itemQuery) {
                // Only show assets that have items that are not approved/in_use
                $itemQuery->whereNotIn('status', ['in_use', 'approved']);
            });

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('asset_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('assetItems', function($itemQuery) use ($request) {
                      $itemQuery->where('asset_tag', 'like', '%' . $request->search . '%')
                               ->orWhere('asset_name', 'like', '%' . $request->search . '%')
                               ->orWhere('serial_number', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->pendingApproval();
            } elseif ($request->status === 'approved') {
                $query->approved();
            } elseif ($request->status === 'rejected') {
                $query->rejected();
            }
        }

        $approvals = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get counts
        $pendingCount = Asset::pendingApproval()
            ->whereHas('assetItems', function($itemQuery) {
                $itemQuery->whereNotIn('status', ['in_use', 'approved']);
            })->count();
        $approvedCount = Asset::approved()->count();

        return Inertia::render('Assets/Approvals', [
            'approvals' => $approvals,
            'filters' => $request->only('search', 'status'),
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
        ]);
    }

    /**
     * Approve an asset (simple approval for new structure)
     */
    public function approve(Asset $asset)
    {
        try {
            if ($asset->status !== 'pending_approval') {
                return back()->withErrors(['error' => 'Asset is not pending approval']);
            }

            $asset->update([
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Update all asset items to approved status
            $asset->assetItems()->update([
                'status' => 'in_use'
            ]);

            // Create history records
            $asset->createHistory([
                'action' => 'asset_approved',
                'action_type' => 'approval',
                'notes' => 'Asset approved by ' . auth()->user()->name,
                'performed_by' => auth()->id(),
                'performed_at' => now(),
            ]);

            foreach ($asset->assetItems as $item) {
                $item->createHistory([
                    'action' => 'item_approved',
                    'action_type' => 'approval',
                    'notes' => 'Asset item approved',
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                ]);
            }

            return back()->with('success', 'Asset approved successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to approve asset: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject an asset (simple rejection for new structure)
     */
    public function reject(Request $request, Asset $asset)
    {
        try {
            if ($asset->status !== 'pending_approval') {
                return back()->withErrors(['error' => 'Asset is not pending approval']);
            }

            $request->validate([
                'rejection_reason' => 'required|string|max:500'
            ]);

            $asset->update([
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Update all asset items to rejected status
            $asset->assetItems()->update([
                'status' => 'pending_approval'
            ]);

            // Create history records
            $asset->createHistory([
                'action' => 'asset_rejected',
                'action_type' => 'rejection',
                'notes' => 'Asset rejected: ' . $request->rejection_reason,
                'performed_by' => auth()->id(),
                'performed_at' => now(),
            ]);

            foreach ($asset->assetItems as $item) {
                $item->createHistory([
                    'action' => 'item_rejected',
                    'action_type' => 'rejection',
                    'notes' => 'Asset item rejected',
                    'performed_by' => auth()->id(),
                    'performed_at' => now(),
                ]);
            }

            return back()->with('success', 'Asset rejected successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to reject asset: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk approve multiple assets
     */
    public function bulkApprove(Request $request)
    {
        try {
            $request->validate([
                'asset_ids' => 'required|array',
                'asset_ids.*' => 'exists:assets,id'
            ]);

            $approvedCount = 0;
            foreach ($request->asset_ids as $assetId) {
                $asset = Asset::find($assetId);
                if ($asset && $asset->status === 'pending_approval') {
                    $asset->update([
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                    $asset->assetItems()->update([
                        'status' => 'in_use'
                    ]);

                    $approvedCount++;
                }
            }

            return back()->with('success', "{$approvedCount} assets approved successfully");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to bulk approve assets: ' . $e->getMessage()]);
        }
    }
}
