<?php

namespace App\Http\Controllers;

use App\Models\LogisticCompany;
use Illuminate\Http\Request;

class LogisticCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = LogisticCompany::query()
            ->when(request()->filled('search'), function ($query) {
                $query->where('name', 'like', '%' . request()->search . '%')
                    ->orWhere('email', 'like', '%' . request()->search . '%');
            })
            ->withCount('drivers')
            ->paginate(request()->input('per_page', 10), ['*'], 'page', request()->input('page', 1))
            ->withQueryString();
        
        $companies->setPath(url()->current()); // Force Laravel to use full URLs

        return inertia('Settings/LogisticCompany/Index', [
            'companies' => $companies,
            'filters' => request()->only('search', 'per_page', 'page')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'nullable|exists:logistic_companies,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:logistic_companies,email,' . $request->id,
                'address' => 'required|string|max:255',
                'incharge_person' => 'required|string|max:255',
                'incharge_phone' => 'required|string|max:20',
                'is_active' => 'boolean'
            ]);

            $company = LogisticCompany::updateOrCreate(
                ['id' => $request->id],
                $validated
            );

            return response()->json([
                'message' => $request->id ? 'Company updated successfully' : 'Company created successfully',
                'company_id' => $company->id
            ], 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LogisticCompany $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:logistic_companies,email,' . $company->id,
            'address' => 'required|string',
            'incharge_person' => 'required|string|max:255',
            'incharge_phone' => 'required|string|max:20'
        ]);

        $company->update($validated);

        return redirect()->back()->with('success', 'Logistic company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LogisticCompany $company)
    {
        try {
            $company->delete();
            return response()->json('Company deleted successfully', 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function toggleStatus(LogisticCompany $company)
    {
        try {
            $company->is_active = !$company->is_active;
            $company->save();
            return response()->json('Status updated successfully', 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
