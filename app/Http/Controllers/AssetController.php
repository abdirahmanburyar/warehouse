<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\SubLocation;
use App\Models\AssetLocation;
use App\Models\Region;
use App\Models\AssetCategory;
use App\Models\CustodyHistory;
use App\Models\FundSource;
use App\Http\Resources\AssetResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $assets = Asset::query();

        if($request->filled('search')){
            $assets->whereLike('asset_tag', '%'.$request->search.'%');
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

        $assets = $assets->with('category:id,name', 'location:id,name', 'subLocation:id,name', 'history','attachments','fundSource')
            ->paginate($request->input('per_page', 2), ['*'], 'page', $request->input('page', 1))
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

    public function show(Request $request, $id)
    {
        $assets = Asset::find($id)->with('category:id,name', 'location:id,name', 'subLocation:id,name', 'history','attachments');

        return inertia('Assets/Show', [
            'assets' => $assets,
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
                'name' => 'required',
            ]);
            
            $region = Region::create([
                'name' => $request->name
            ]);
            
            return response()->json($region, 200);
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
        $locations = AssetLocation::all();
        $categories = AssetCategory::all();
        return Inertia::render('Assets/Edit', [
            'asset' => $asset,
            'locations' => $locations,
            'categories' => $categories
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
                'sub_location_id' => 'nullable',
                'fund_source_id' => 'required',
                'acquisition_date' => 'required|date',
                'status' => 'required|string|in:active,in_use,maintenance,retired,disposed',
                'original_value' => 'required|numeric|min:0',
                'source_agency' => 'required|string|max:255'
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
        return redirect()->route('assets.index');
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
}
