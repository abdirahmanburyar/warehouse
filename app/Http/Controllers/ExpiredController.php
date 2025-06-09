<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpiredResource;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Disposal;
use App\Models\Transfer;
use App\Models\Facility;
use App\Models\Category;
use App\Models\Dosage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExpiredController extends Controller
{
    public function index(Request $request) {
        $now = Carbon::now();
        $sixMonthsFromNow = $now->copy()->addMonths(6);
        $oneYearFromNow = $now->copy()->addYear();
    
        $query = Inventory::query();
    
        $query->with(['product.dosage:id,name', 'product.category:id,name', 'warehouse', 'location:id,location']);
    
        $query->where('quantity', '>', 0)
              ->where(function($q) use ($now, $oneYearFromNow) {
                  $q->where('expiry_date', '<=', $oneYearFromNow)
                    ->orWhere('expiry_date', '<', $now);
              });
    
        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('barcode', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($prodQ) use ($search) {
                      $prodQ->where('name', 'like', "%{$search}%");
                  });
            });
        }
    
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
    
        if ($request->filled('category')) {
            $query->whereHas('product.category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }
    
        if ($request->filled('dosage')) {
            $query->whereHas('product.dosage', function ($q) use ($request) {
                $q->where('name', $request->dosage);
            });
        }
    
        if ($request->filled('warehouse')) {
            $query->whereHas('warehouse', function($q) use($request){
                $q->where('name', 'like', "%{$request->warehouse}%");
            });
        }
    
        if ($request->filled('location')) {
            $query->whereHas('location', function($q) use($request){
                $q->where('location', 'like', "%{$request->location}%");
            });
        }
    
        // ✅ Paginate while still a query builder
        $paginatedInventories = $query->paginate(
            $request->input('per_page', 2),
            ['*'],
            'page',
            $request->input('page', 1)
        )->withQueryString();
    
        $paginatedInventories->setPath(url()->current());
    
        // Map inventory data for expiry-related flags
        $paginatedInventories->getCollection()->transform(function($inventory) {
            $inventory->expiry_date = Carbon::parse($inventory->expiry_date);
            $now = Carbon::now(); // ✅ keep it as Carbon
        
            $inventory->expired = $inventory->expiry_date->lt($now);
        
            // Days until expiry: negative if expired, positive if in future
            $inventory->days_until_expiry = intval($now->diffInDays($inventory->expiry_date, false));
                    
            // Normalize other flags
            $inventory->disposed = (bool) $inventory->disposed;
            $inventory->expiring_soon = !$inventory->expired && $inventory->days_until_expiry <= 180;
            
            return $inventory;
        });
    
        $products = Product::select('id', 'name')->get();
        $warehouses = Warehouse::pluck('name')->toArray();
        $category = Category::pluck('name')->toArray();
        $dosage = Dosage::pluck('name')->toArray();
    
        return inertia('Expired/Index', [
            'inventories' => ExpiredResource::collection($paginatedInventories),
            'summary' => [
                'total' => $paginatedInventories->total(),
                'expiring_within_6_months' => $paginatedInventories->getCollection()->where('expiring_soon', true)->count(),
                'expiring_within_1_year' => $paginatedInventories->getCollection()->where('expired', false)->where('days_until_expiry', '<=', 365)->count(),
                'expired' => $paginatedInventories->getCollection()->where('expired', true)->count(),
            ],
            'products' => $products,
            'warehouses' => $warehouses,
            'categories' => $category,
            'dosage' => $dosage,
            'filters' => $request->only('search', 'product_id', 'warehouse', 'dosage','category', 'location', 'batch_number', 'expiry_date_from', 'expiry_date_to', 'per_page', 'page'),
        ]);
    }
    

    public function dispose(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'id' => 'required|exists:inventories,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'status' => 'required|string',
                'note' => 'required|string|max:255',
                'barcode' => 'required|string',
                'expiry_date' => 'required|date',
                'batch_number' => 'required|string',
                'uom' => 'required|string',
                'attachments' => 'nullable|array',
                'attachments.*' => 'nullable|file|mimes:pdf', // Max 10MB per file
            ]);
            
            // Start a database transaction
            DB::beginTransaction();
            
            // Get the inventory to include its number in the note
            $inventory = Inventory::find($request->id);
            
            // Generate note based on condition and source
            $note = "Inventory ($inventory->id) - {$request->status}";
            if ($request->note) {
                $note .= " - {$request->note}";
            }
            
            // Handle file attachments if any
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $index => $file) {
                    if ($file->isValid()) {
                        $fileName = 'disposal_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('attachments/disposals'), $fileName);
                        $attachments[] = [
                            'name' => $file->getClientOriginalName(),
                            'path' => '/attachments/disposals/' . $fileName,
                            'type' => $file->getClientMimeType(),
                            'size' => filesize(public_path('attachments/disposals/' . $fileName)),
                            'uploaded_at' => now()->toDateTimeString()
                        ];
                    }
                }
            }
            
            // Create a new liquidation record
            $disposal = Disposal::create([
                'inventory_id' => $request->id,
                'product_id' => $request->product_id,
                'disposed_by' => auth()->id(),
                'disposed_at' => Carbon::now(),
                'quantity' => $request->quantity,
                'status' => 'pending', // Default status is pending
                'note' => $note,
                'barcode' => $request->barcode,
                'expire_date' => $request->expiry_date,
                'batch_number' => $request->batch_number,
                'uom' => $request->uom,
                'attachments' => !empty($attachments) ? json_encode($attachments) : null,
            ]);
            
            // Update the inventory quantity
            $inventory->update([
                'quantity' => $inventory->quantity - $request->quantity
            ]);
            
            // Commit the transaction
            DB::commit();
            
            return response()->json('Item has been disposed successfully', 200);
        } catch (\Throwable $e) {
            DB::rollback();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function transfer(Request $request, $inventory)
    {
        if ($request->isMethod('get')) {
            $inv = Inventory::with('product','location','warehouse')->find($inventory);
            $facilities = Facility::get();
            $warehouses = Warehouse::get();
            if (!$inv) {
                return redirect()->route('expired.index')->with('error', 'Inventory not found');
            }

            return inertia("Expired/Transfer", [
                "inventory" => $inv,
                'facilities' => $facilities,
                'warehouses' => $warehouses
            ]);
        }

        $request->validate([
            'destination_type' => 'required|in:warehouse,facility',
            'destination_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $inventory = Inventory::findOrFail($inventory);
            
            if ($request->quantity > $inventory->quantity) {
                return response()->json([
                    'message' => 'Transfer quantity cannot exceed available quantity'
                ], 422);
            }

            // Generate transfer ID
            $transferId = ExpiredTransfer::generateTransferId();

            // Create transfer record
            $transfer = ExpiredTransfer::create([
                'transfer_id' => $transferId,
                'inventory_id' => $inventory->id,
                'destination_type' => $request->destination_type,
                'destination_id' => $request->destination_id,
                'quantity' => $request->quantity,
                'notes' => $request->notes,
                'transferred_by' => Auth::id(),
                'status' => 'completed'
            ]);

            // Update inventory quantity
            $inventory->update([
                'quantity' => $inventory->quantity - $request->quantity
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Transfer completed successfully',
                'transfer_id' => $transferId
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to process transfer. ' . $e->getMessage()
            ], 500);
        }
    }
}