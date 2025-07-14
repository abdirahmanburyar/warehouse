<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\SubLocation;
use App\Models\AssetLocation;
use App\Models\Region;
use App\Models\AssetCategory;
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

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $assets = Asset::query();

        if($request->filled('search')){
            $search = $request->search;
            $assets->where(function($query) use ($search) {
                $query->whereLike('asset_tag', '%'.$search.'%')
                      ->orWhereLike('serial_number', '%'.$search.'%')
                      ->orWhereLike('item_description', '%'.$search.'%')
                      ->orWhereLike('person_assigned', '%'.$search.'%');
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

        $assets = $assets->with('category:id,name', 'location:id,name', 'subLocation:id,name', 'region:id,name', 'history','attachments','fundSource')
            ->paginate($request->input('per_page', 10), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        $assets->setPath(url()->current()); // Force Laravel to use full URLs

        $count = Asset::count();

        $locations = AssetLocation::with('subLocations')->get();
        return inertia('Assets/Index', [
            'locations' => $locations,
            'assets' => AssetResource::collection($assets),
            'filters' => $request->only('page','per_page','search','region_id','location_id','sub_location_id','fund_source_id'),
            'assetsCount' => $count,
            'regions' => Region::get(),
            'fundSources' => FundSource::get(),
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
        return Inertia::render('Assets/Create', [
            'locations' => $locations,
            'categories' => $categories,
            'fundSources' => $fundSources,
            'regions' => $regions
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
            Excel::import(new AssetsImport, $request->file('file'));
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
                'item_description' => 'required|string',
                'person_assigned' => 'required',
                'acquisition_date' => 'required|date',
                'status' => 'required|string|in:active,in_use,maintenance,retired,disposed',
                'original_value' => 'required|numeric|min:0',
                'has_warranty' => 'required|in:0,1,true,false',
                'has_documents' => 'required|in:0,1,true,false',
                'asset_warranty_start' => 'nullable|date|required_if:has_warranty,1',
                'asset_warranty_end' => 'nullable|date|required_if:has_warranty,1|after_or_equal:asset_warranty_start',
                'documents' => 'array|required_if:has_documents,1',
                'documents.*.type' => 'required_if:has_documents,1|string',
                'documents.*.file' => 'nullable'
            ]);
            
            // Convert string boolean values to actual booleans
            $hasWarranty = filter_var($request->has_warranty, FILTER_VALIDATE_BOOLEAN);
            $hasDocuments = filter_var($request->has_documents, FILTER_VALIDATE_BOOLEAN);
            
            // Create asset data array with the correct IDs
            $assetData = [
                'asset_tag' => $request->asset_tag,
                'asset_category_id' => $request->asset_category_id,
                'serial_number' => $request->serial_number,
                'item_description' => $request->item_description,
                'person_assigned' => $request->person_assigned,
                'asset_location_id' => $request->asset_location_id,
                'sub_location_id' => $request->sub_location_id,
                'fund_source_id' => $request->fund_source_id,
                'region_id' => $request->region_id,
                'acquisition_date' => $request->acquisition_date,
                'status' => $request->status,
                'original_value' => $request->original_value,
                'has_warranty' => $hasWarranty,
                'has_documents' => $hasDocuments,
                'asset_warranty_start' => $hasWarranty ? $request->asset_warranty_start : null,
                'asset_warranty_end' => $hasWarranty ? $request->asset_warranty_end : null
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
        $asset = Asset::with('category:id,name', 'location:id,name', 'subLocation:id,name', 'history', 'attachments', 'fundSource', 'region')
            ->findOrFail($asset->id);
        $locations = AssetLocation::all();
        $categories = AssetCategory::all();
        $fundSources = fundSource::get();
        $regions = Region::get();
        return Inertia::render('Assets/Edit', [
            'asset' => $asset,
            'locations' => $locations,
            'categories' => $categories,
            'fundSources' => $fundSources,
            'regions' => $regions
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        try {
            $validated = $request->validate([
                'asset_tag' => 'required|string|max:255',
                'asset_category_id' => 'required|exists:asset_categories,id',
                'serial_number' => 'required|string|max:255|unique:assets,serial_number,' . $asset->id,
                'item_description' => 'required|string',
                'person_assigned' => 'required',
                'asset_location_id' => 'required',
                'sub_location_id' => 'required',
                'region_id' => 'required',
                'fund_source_id' => 'required',
                'acquisition_date' => 'required|date',
                'status' => 'required|string|in:active,in_use,maintenance,retired,disposed',
                'original_value' => 'required|numeric|min:0'
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
    public function transferAsset(Request $request)
    {
        try {
            return DB::transaction(function() use($request) {
                $validated = $request->validate([
                    'asset_id' => 'required|exists:assets,id',
                    'custodian' => 'required|string|max:255',
                    'transfer_date' => 'required|date',
                    'assignment_notes' => 'nullable|string',
                ]);

                $asset = Asset::findOrFail($validated['asset_id']);
                
                // Check if asset is approved and in use
                if ($asset->status !== Asset::STATUS_IN_USE && $asset->status !== Asset::STATUS_ACTIVE) {
                    return response()->json('Asset must be approved and in use before transfer', 400);
                }

                // Create transfer approval
                $transferApproval = $asset->approvals()->create([
                    'role_id' => 2, // asset_manager role ID - adjust based on your role structure
                    'action' => 'transfer_approval',
                    'sequence' => 1,
                    'status' => 'pending',
                    'created_by' => auth()->id(),
                    'notes' => "Transfer request: {$asset->person_assigned} → {$validated['custodian']}"
                ]);

                // Store transfer data in the approval
                $transferData = [
                    'old_custodian' => $asset->person_assigned,
                    'new_custodian' => $validated['custodian'],
                    'transfer_date' => $validated['transfer_date'],
                    'assignment_notes' => $validated['assignment_notes'] ?? null,
                ];
                
                $transferApproval->update([
                    'transfer_data' => json_encode($transferData)
                ]);

                return response()->json([
                    'message' => 'Transfer request submitted for approval',
                    'transfer_approval' => $transferApproval,
                    'asset' => $asset->fresh()
                ], 200);
            });
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
                'action' => 'required|in:approve,reject',
                'notes' => 'nullable|string'
            ]);

            $approval = AssetApproval::findOrFail($validated['approval_id']);
            
            if ($approval->action !== 'transfer_approval') {
                return response()->json('Invalid approval type', 400);
            }

            if ($validated['action'] === 'approve') {
                // Get transfer data
                $transferData = json_decode($approval->transfer_data, true);
                
                // Update asset
                $asset->update([
                    'person_assigned' => $transferData['new_custodian'],
                    'transfer_date' => $transferData['transfer_date']
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

                return response()->json([
                    'message' => 'Transfer approved successfully',
                    'asset' => $asset->fresh(),
                    'custody_history' => $custodyHistory
                ], 200);
            } else {
                // Reject transfer
                $approval->update([
                    'status' => 'rejected',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'notes' => $validated['notes'] ?? null
                ]);

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
            $validated = $request->validate([
                'notes' => 'nullable|string',
                'action' => 'required|in:approve,reject,review'
            ]);

            $action = $validated['action'];
            $user = auth()->user();

            // Check permissions based on action
            if ($action === 'approve' && !$user->can('asset_approve')) {
                return response()->json('You are not authorized to approve assets', 403);
            }

            if ($action === 'review' && !$user->can('asset_review')) {
                return response()->json('You are not authorized to review assets', 403);
            }

            // For reject, no specific permission check needed as mentioned
            if ($action === 'reject') {
                // Reject the asset and rollback to active status
                $asset->update([
                    'status' => Asset::STATUS_ACTIVE,
                    'submitted_for_approval' => false
                ]);

                // Update any pending approvals to rejected
                $asset->approvals()->where('status', 'pending')->update([
                    'status' => 'rejected',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'notes' => $validated['notes'] ?? 'Rejected by ' . $user->name
                ]);

                return response()->json([
                    'message' => 'Asset rejected and returned to active status',
                    'asset' => $asset->fresh()
                ], 200);
            }

            if ($action === 'review') {
                // For review, just add notes without changing status
                $asset->approvals()->where('status', 'pending')->update([
                    'notes' => ($asset->approvals()->where('status', 'pending')->first()->notes ?? '') . 
                               "\n\nReviewed by " . $user->name . " on " . now()->format('Y-m-d H:i:s') . 
                               ": " . ($validated['notes'] ?? 'No additional notes')
                ]);

                return response()->json([
                    'message' => 'Asset reviewed successfully',
                    'asset' => $asset->fresh()
                ], 200);
            }

            if ($action === 'approve') {
                // Approve the asset
                $asset->update([
                    'status' => Asset::STATUS_IN_USE,
                    'submitted_for_approval' => false
                ]);

                // Update all pending approvals to approved
                $asset->approvals()->where('status', 'pending')->update([
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'notes' => $validated['notes'] ?? 'Approved by ' . $user->name
                ]);

                return response()->json([
                    'message' => 'Asset approved and ready for use',
                    'asset' => $asset->fresh()
                ], 200);
            }

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Get pending approvals for current user
     */
    public function getPendingApprovals(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Get all pending assets that the user can review or approve
            $pendingAssets = Asset::where('submitted_for_approval', true)
                ->where('status', Asset::STATUS_PENDING_APPROVAL)
                ->with(['approvals' => function($query) {
                    $query->where('status', 'pending');
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
     * Display the approvals index page
     */
    public function approvalsIndex(Request $request)
    {
        $query = AssetApproval::with(['approvable', 'role', 'approver', 'creator'])
            ->where('approvable_type', Asset::class);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('approvable', function($assetQuery) use ($search) {
                    $assetQuery->where('asset_tag', 'like', '%' . $search . '%')
                              ->orWhere('serial_number', 'like', '%' . $search . '%')
                              ->orWhere('item_description', 'like', '%' . $search . '%');
                })
                ->orWhereHas('creator', function($creatorQuery) use ($search) {
                    $creatorQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhereHas('approver', function($approverQuery) use ($search) {
                    $approverQuery->where('name', 'like', '%' . $search . '%')
                                 ->orWhere('email', 'like', '%' . $search . '%');
                });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }



        // Apply action type filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Apply date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }



        $approvals = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get counts
        $pendingCount = AssetApproval::where('approvable_type', Asset::class)
            ->where('status', 'pending')->count();
        $approvedCount = AssetApproval::where('approvable_type', Asset::class)
            ->where('status', 'approved')->count();

        // Get roles for filter
        $roles = \App\Models\Role::all();

        return Inertia::render('Assets/Approvals', [
            'approvals' => $approvals,
            'roles' => $roles,
            'filters' => $request->only('search', 'status', 'action', 'date_from', 'date_to'),
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
        ]);
    }
}
