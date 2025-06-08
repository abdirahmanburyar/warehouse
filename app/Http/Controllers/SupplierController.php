<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return redirect()->route('supplies.index', ['tab' => 'suppliers']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|exists:suppliers,id',
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'status' => 'string',
            ]);

        $supplier = Supplier::updateOrCreate(
            ['id' => $request->id],
            $validated
        );

        return response()->json($request->id ? 'updated' : 'created', 200);
        } catch (\Throwable $e) {
            return response()->json( $e->getMessage(), 500);
        }
    }

    public function create(Request $request){

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            if ($supplier->supplies()->count() > 0) {
                return response()->json('Cannot delete supplier with associated supplies.', 500);
            }

            $supplier->delete();

            return response()->json('Supplier deleted successfully.', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function edit(Request $request, $id){
        $supplier = Supplier::find($id);
        return inertia('Supplies/Edit', [
            'supplier' => $supplier
        ]);
    }
}
