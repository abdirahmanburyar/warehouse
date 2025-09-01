<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AssetDepreciationSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AssetDepreciationSettingsController extends Controller
{
    /**
     * Display the settings index page
     */
    public function index()
    {
        $settings = AssetDepreciationSetting::orderBy('category')
            ->orderBy('key')
            ->get()
            ->groupBy('category');

        return Inertia::render('Settings/AssetDepreciation/Index', [
            'settings' => $settings,
            'categories' => [
                'default' => 'Default Settings',
                'category_override' => 'Category Overrides',
                'method' => 'Depreciation Methods',
                'system' => 'System Settings',
            ],
            'types' => [
                'integer' => 'Integer',
                'float' => 'Decimal',
                'string' => 'Text',
                'boolean' => 'Yes/No',
                'json' => 'JSON Data',
            ],
        ]);
    }

    /**
     * Show the form for creating a new setting
     */
    public function create()
    {
        return Inertia::render('Settings/AssetDepreciation/Create', [
            'categories' => [
                'default' => 'Default Settings',
                'category_override' => 'Category Overrides',
                'method' => 'Depreciation Methods',
                'system' => 'System Settings',
            ],
            'types' => [
                'integer' => 'Integer',
                'float' => 'Decimal',
                'string' => 'Text',
                'boolean' => 'Yes/No',
                'json' => 'JSON Data',
            ],
            'depreciationMethods' => [
                'straight_line' => 'Straight Line',
                'declining_balance' => 'Declining Balance',
                'sum_of_years' => 'Sum of Years',
            ],
            'assetCategories' => $this->getAssetCategories(),
        ]);
    }

    /**
     * Store a newly created setting
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:asset_depreciation_settings,key',
            'value' => 'required',
            'type' => ['required', Rule::in(['integer', 'float', 'string', 'boolean', 'json'])],
            'description' => 'nullable|string|max:1000',
            'category' => ['required', Rule::in(['default', 'category_override', 'method', 'system'])],
            'metadata' => 'nullable|array',
        ]);

        try {
            $setting = AssetDepreciationSetting::create([
                'key' => $request->key,
                'value' => $request->value,
                'type' => $request->type,
                'description' => $request->description,
                'category' => $request->category,
                'metadata' => $request->metadata,
                'is_active' => true,
            ]);

            Log::info('Asset depreciation setting created', [
                'key' => $setting->key,
                'category' => $setting->category,
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('settings.asset-depreciation.index')
                ->with('success', 'Setting created successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to create asset depreciation setting', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return back()->withErrors(['error' => 'Failed to create setting: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified setting
     */
    public function edit(AssetDepreciationSetting $setting)
    {
        return Inertia::render('Settings/AssetDepreciation/Edit', [
            'setting' => $setting,
            'categories' => [
                'default' => 'Default Settings',
                'category_override' => 'Category Overrides',
                'method' => 'Depreciation Methods',
                'system' => 'System Settings',
            ],
            'types' => [
                'integer' => 'Integer',
                'float' => 'Decimal',
                'string' => 'Text',
                'boolean' => 'Yes/No',
                'json' => 'JSON Data',
            ],
            'depreciationMethods' => [
                'straight_line' => 'Straight Line',
                'declining_balance' => 'Declining Balance',
                'sum_of_years' => 'Sum of Years',
            ],
            'assetCategories' => $this->getAssetCategories(),
        ]);
    }

    /**
     * Update the specified setting
     */
    public function update(Request $request, AssetDepreciationSetting $setting)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:asset_depreciation_settings,key,' . $setting->id,
            'value' => 'required',
            'type' => ['required', Rule::in(['integer', 'float', 'string', 'boolean', 'json'])],
            'description' => 'nullable|string|max:1000',
            'category' => ['required', Rule::in(['default', 'category_override', 'method', 'system'])],
            'metadata' => 'nullable|array',
        ]);

        try {
            $oldValue = $setting->value;
            
            $setting->update([
                'key' => $request->key,
                'value' => $request->value,
                'type' => $request->type,
                'description' => $request->description,
                'category' => $request->category,
                'metadata' => $request->metadata,
            ]);

            Log::info('Asset depreciation setting updated', [
                'key' => $setting->key,
                'old_value' => $oldValue,
                'new_value' => $request->value,
                'updated_by' => auth()->id(),
            ]);

            return redirect()->route('settings.asset-depreciation.index')
                ->with('success', 'Setting updated successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to update asset depreciation setting', [
                'error' => $e->getMessage(),
                'setting_id' => $setting->id,
                'data' => $request->all(),
            ]);

            return back()->withErrors(['error' => 'Failed to update setting: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified setting
     */
    public function destroy(AssetDepreciationSetting $setting)
    {
        try {
            $setting->delete();

            Log::info('Asset depreciation setting deleted', [
                'key' => $setting->key,
                'deleted_by' => auth()->id(),
            ]);

            return redirect()->route('settings.asset-depreciation.index')
                ->with('success', 'Setting deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to delete asset depreciation setting', [
                'error' => $e->getMessage(),
                'setting_id' => $setting->id,
            ]);

            return back()->withErrors(['error' => 'Failed to delete setting: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle setting active status
     */
    public function toggleStatus(AssetDepreciationSetting $setting)
    {
        try {
            $setting->update([
                'is_active' => !$setting->is_active
            ]);

            $status = $setting->is_active ? 'enabled' : 'disabled';

            Log::info('Asset depreciation setting status toggled', [
                'key' => $setting->key,
                'new_status' => $status,
                'toggled_by' => auth()->id(),
            ]);

            return back()->with('success', "Setting {$status} successfully.");

        } catch (\Exception $e) {
            Log::error('Failed to toggle asset depreciation setting status', [
                'error' => $e->getMessage(),
                'setting_id' => $setting->id,
            ]);

            return back()->withErrors(['error' => 'Failed to toggle setting status: ' . $e->getMessage()]);
        }
    }

    /**
     * Install default settings
     */
    public function installDefaults()
    {
        try {
            AssetDepreciationSetting::installDefaults();

            Log::info('Asset depreciation default settings installed', [
                'installed_by' => auth()->id(),
            ]);

            return back()->with('success', 'Default settings installed successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to install asset depreciation default settings', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to install default settings: ' . $e->getMessage()]);
        }
    }

    /**
     * Reset all settings to defaults
     */
    public function resetToDefaults()
    {
        try {
            // Delete all existing settings
            AssetDepreciationSetting::truncate();
            
            // Install defaults
            AssetDepreciationSetting::installDefaults();

            Log::info('Asset depreciation settings reset to defaults', [
                'reset_by' => auth()->id(),
            ]);

            return back()->with('success', 'Settings reset to defaults successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to reset asset depreciation settings to defaults', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to reset settings: ' . $e->getMessage()]);
        }
    }

    /**
     * Export settings configuration
     */
    public function export()
    {
        try {
            $config = AssetDepreciationSetting::getConfigurationArray();
            
            $filename = 'asset-depreciation-config-' . date('Y-m-d-H-i-s') . '.json';
            
            return response()->json($config)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            Log::error('Failed to export asset depreciation settings', [
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to export settings: ' . $e->getMessage()]);
        }
    }

    /**
     * Get asset categories for category override settings
     */
    private function getAssetCategories()
    {
        // This would typically come from your AssetCategory model
        // For now, returning common categories
        return [
            'computers' => 'Computers & Technology',
            'furniture' => 'Furniture & Fixtures',
            'vehicles' => 'Vehicles & Transportation',
            'buildings' => 'Buildings & Structures',
            'machinery' => 'Machinery & Equipment',
            'office_equipment' => 'Office Equipment',
            'tools' => 'Tools & Instruments',
            'software' => 'Software & Licenses',
            'land' => 'Land & Real Estate',
            'intangible' => 'Intangible Assets',
        ];
    }

    /**
     * Get current configuration for API access
     */
    public function getConfiguration()
    {
        try {
            $config = AssetDepreciationSetting::getConfigurationArray();
            
            return response()->json([
                'success' => true,
                'data' => $config,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get asset depreciation configuration', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get configuration: ' . $e->getMessage(),
            ], 500);
        }
    }
}
