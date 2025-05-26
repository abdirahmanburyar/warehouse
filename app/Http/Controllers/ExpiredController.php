<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpiredResource;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Disposal;
use App\Models\Transfer;
use App\Models\Facility;
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

        $inventories = Inventory::query()
            ->select('inventories.*', 'products.name as product_name')
            ->join('products', 'products.id', '=', 'inventories.product_id')
            ->where('quantity', '>', 0)
            ->where(function($query) use ($now, $oneYearFromNow) {
                $query->where('expiry_date', '<=', $oneYearFromNow) // Items expiring within next year
                      ->orWhere('expiry_date', '<', $now); // Already expired items
            })
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function($inventory) use ($now) {
                // Calculate days until expiry
                $expiryDate = Carbon::parse($inventory->expiry_date);
                $daysUntilExpiry = ceil($now->floatDiffInDays($expiryDate, false));
                
                $inventory->expired = $expiryDate < $now;
                $inventory->days_until_expiry = $daysUntilExpiry;
                $inventory->disposed = (bool) $inventory->disposed;
                
                // Only mark as expiring soon if not expired and within 6 months
                $inventory->expiring_soon = !$inventory->expired && $daysUntilExpiry > 0 && $daysUntilExpiry <= 180;
                return $inventory;
            });

        return inertia('Expired/Index', [
            'inventories' => $inventories,
            'summary' => [
                'total' => $inventories->count(),
                'expiring_within_6_months' => $inventories->where('expiring_soon', true)->count(),
                'expiring_within_1_year' => $inventories->where('expired', false)
                    ->where('days_until_expiry', '<=', 365)->count(),
                'expired' => $inventories->where('expired', true)->count()
            ]
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
                'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB per file
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
                    $fileName = 'liquidate_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('attachments/disposals', $fileName, 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
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