<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Dosage;
use App\Models\Inventory;
use App\Models\Supply;
use App\Models\SupplyItem;
use App\Models\SubCategory;
use App\Models\WarehouseAmc;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'productID',
        'name',
        'category_id',
        'dosage_id',
        // 'movement',
        'is_active',
        'tracert_type'
    ];

    protected $casts = [
        'tracert_type' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            // Find the highest productID in the database
            $maxProductId = self::max('productID');
            
            // If there are existing products, increment the highest productID
            if ($maxProductId) {
                $nextId = (int)$maxProductId + 1;
            } else {
                // Start from 1 if no products exist
                $nextId = 1;
            }
            
            // Format as 6-digit number with leading zeros
            $product->productID = str_pad($nextId, 6, '0', STR_PAD_LEFT);
        });
    }



    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function dosage()
    {
        return $this->belongsTo(Dosage::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    /**
     * Get the supply items that contain this product.
     */
    public function supplyItems()
    {
        return $this->hasMany(SupplyItem::class);
    }

    /**
     * Get the supplies that contain this product.
     */
    public function supplies()
    {
        return $this->belongsToMany(Supply::class, 'supply_items')
            ->withPivot(['quantity', 'status'])
            ->withTimestamps();
    }

    // reorderLevel
    public function reorderLevel()
    {
        return $this->hasOne(ReorderLevel::class);
    }

    /**
     * Get the inventories for the product.
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function items(){
        return $this->hasMany(InventoryItem::class);
    }

    public function eligible(){
        return $this->hasMany(EligibleItem::class);
    }

    public function facilityInventories(){
        return $this->hasMany(FacilityInventory::class);
    }

    /**
     * Get the warehouse AMCs for this product.
     */
    public function warehouseAmcs()
    {
        return $this->hasMany(\App\Models\WarehouseAmc::class);
    }

    /**
     * Calculate AMC using percentage deviation screening (same logic as WarehouseAmcController)
     */
    public function calculateAMC()
    {
        try {
            // Get all consumption values for the product from warehouse_amcs table
            $consumptionsWithMonth = $this->warehouseAmcs()
                ->where('quantity', '>', 0) // Only consider positive consumption values
                ->orderBy('month_year', 'desc') // Most recent first
                ->get(['month_year', 'quantity']);

            // If we have less than 3 values, return 0
            if ($consumptionsWithMonth->count() < 3) {
                return 0;
            }

            // Extract quantities and months
            $quantities = $consumptionsWithMonth->pluck('quantity')->values();
            $months = $consumptionsWithMonth->pluck('month_year')->values();
            
            // Start with the 3 most recent months
            $selectedMonths = [];
            $passedMonths = [];
            $failedMonths = [];
            
            // Initial selection: 3 most recent months
            for ($i = 0; $i < 3; $i++) {
                $selectedMonths[] = [
                    'month' => $months[$i],
                    'quantity' => $quantities[$i]
                ];
            }
            
            $attempt = 1;
            $maxAttempts = 10; // Prevent infinite loops
            
            while ($attempt <= $maxAttempts) {
                // Calculate average of selected months
                $average = collect($selectedMonths)->avg('quantity');
                
                // Check each month's deviation
                $allPassed = true;
                $newPassedMonths = [];
                $newFailedMonths = [];
                
                foreach ($selectedMonths as $monthData) {
                    $quantity = $monthData['quantity'];
                    $deviation = abs($quantity - $average) / $average * 100;
                    
                    if ($deviation <= 70) {
                        // Month passed screening
                        $newPassedMonths[] = $monthData;
                    } else {
                        // Month failed screening
                        $newFailedMonths[] = $monthData;
                        $allPassed = false;
                    }
                }
                
                // Add newly passed months to the global passed list
                foreach ($newPassedMonths as $monthData) {
                    if (!collect($passedMonths)->contains('month', $monthData['month'])) {
                        $passedMonths[] = $monthData;
                    }
                }
                
                // Add newly failed months to the global failed list
                foreach ($newFailedMonths as $monthData) {
                    if (!collect($failedMonths)->contains('month', $monthData['month'])) {
                        $failedMonths[] = $monthData;
                    }
                }
                
                // If all months passed, we're done
                if ($allPassed) {
                    break;
                }
                
                // If we have 3 or more passed months, use them
                if (count($passedMonths) >= 3) {
                    $selectedMonths = array_slice($passedMonths, 0, 3);
                    break;
                }
                
                // Need to reselect months including passed ones
                $newSelection = [];
                
                // First, include all passed months
                foreach ($passedMonths as $monthData) {
                    $newSelection[] = $monthData;
                }
                
                // Then add more months from the original list until we have 3
                $monthIndex = 0;
                while (count($newSelection) < 3 && $monthIndex < count($quantities)) {
                    $monthData = [
                        'month' => $months[$monthIndex],
                        'quantity' => $quantities[$monthIndex]
                    ];
                    
                    // Only add if not already in selection and not in failed months
                    $alreadySelected = collect($newSelection)->contains('month', $monthData['month']);
                    $isFailed = collect($failedMonths)->contains('month', $monthData['month']);
                    
                    if (!$alreadySelected && !$isFailed) {
                        $newSelection[] = $monthData;
                    }
                    
                    $monthIndex++;
                }
                
                // Update selected months for next iteration
                $selectedMonths = $newSelection;
                $attempt++;
            }
            
            // Calculate final AMC
            if (count($selectedMonths) >= 3) {
                $amc = collect($selectedMonths)->avg('quantity');
                $result = round($amc, 2);
                return $result;
            } else {
                return 0;
            }

        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calculate buffer stock: (Max AMC - AMC) × 3
     * Max AMC is the highest value from the selected months that passed screening
     */
    public function calculateBufferStock()
    {
        try {
            $amc = $this->calculateAMC();
            if ($amc == 0) {
                return 0;
            }

            // Get the selected months that passed screening (same logic as calculateAMC)
            $consumptionsWithMonth = $this->warehouseAmcs()
                ->where('quantity', '>', 0)
                ->orderBy('month_year', 'desc')
                ->get(['month_year', 'quantity']);

            if ($consumptionsWithMonth->count() < 3) {
                return 0;
            }

            // Use the same screening logic to get selected months
            $quantities = $consumptionsWithMonth->pluck('quantity')->values();
            $months = $consumptionsWithMonth->pluck('month_year')->values();
            
            $selectedMonths = [];
            $passedMonths = [];
            $failedMonths = [];
            
            // Initial selection: 3 most recent months
            for ($i = 0; $i < 3; $i++) {
                $selectedMonths[] = [
                    'month' => $months[$i],
                    'quantity' => $quantities[$i]
                ];
            }
            
            $attempt = 1;
            $maxAttempts = 10;
            
            while ($attempt <= $maxAttempts) {
                $average = collect($selectedMonths)->avg('quantity');
                $allPassed = true;
                $newPassedMonths = [];
                $newFailedMonths = [];
                
                foreach ($selectedMonths as $monthData) {
                    $quantity = $monthData['quantity'];
                    $deviation = abs($quantity - $average) / $average * 100;
                    
                    if ($deviation <= 70) {
                        $newPassedMonths[] = $monthData;
                    } else {
                        $newFailedMonths[] = $monthData;
                        $allPassed = false;
                    }
                }
                
                foreach ($newPassedMonths as $monthData) {
                    if (!collect($passedMonths)->contains('month', $monthData['month'])) {
                        $passedMonths[] = $monthData;
                    }
                }
                
                foreach ($newFailedMonths as $monthData) {
                    if (!collect($failedMonths)->contains('month', $monthData['month'])) {
                        $failedMonths[] = $monthData;
                    }
                }
                
                if ($allPassed) {
                    break;
                }
                
                if (count($passedMonths) >= 3) {
                    $selectedMonths = array_slice($passedMonths, 0, 3);
                    break;
                }
                
                $newSelection = [];
                foreach ($passedMonths as $monthData) {
                    $newSelection[] = $monthData;
                }
                
                $monthIndex = 0;
                while (count($newSelection) < 3 && $monthIndex < count($quantities)) {
                    $monthData = [
                        'month' => $months[$monthIndex],
                        'quantity' => $quantities[$monthIndex]
                    ];
                    
                    $alreadySelected = collect($newSelection)->contains('month', $monthData['month']);
                    $isFailed = collect($failedMonths)->contains('month', $monthData['month']);
                    
                    if (!$alreadySelected && !$isFailed) {
                        $newSelection[] = $monthData;
                    }
                    
                    $monthIndex++;
                }
                
                $selectedMonths = $newSelection;
                $attempt++;
            }
            
            if (count($selectedMonths) >= 3) {
                // Find the maximum value from selected months
                $maxAMC = max(array_column($selectedMonths, 'quantity'));
                
                // Calculate buffer stock: (Max AMC - AMC) × 3
                $bufferStock = ($maxAMC - $amc) * 3;
                return round(max(0, $bufferStock), 2);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calculate reorder level: (AMC × 3) + Buffer Stock
     */
    public function calculateReorderLevel()
    {
        try {
            $amc = $this->calculateAMC();
            if ($amc == 0) {
                return 0;
            }
            
            $bufferStock = $this->calculateBufferStock();
            
            // Calculate reorder level: (AMC × 3) + Buffer Stock
            $reorderLevel = ($amc * 3) + $bufferStock;
            return round($reorderLevel, 2);
            
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Optimized method to calculate AMC, Buffer Stock, and Reorder Level in one go
     * This avoids duplicate database queries and improves performance
     */
    public function calculateInventoryMetrics()
    {
        try {
            // Get all consumption values for the product from warehouse_amcs table
            $consumptionsWithMonth = $this->warehouseAmcs()
                ->where('quantity', '>', 0) // Only consider positive consumption values
                ->orderBy('month_year', 'desc') // Most recent first
                ->get(['month_year', 'quantity']);

            // If we have less than 3 values, return default values
            if ($consumptionsWithMonth->count() < 3) {
                return [
                    'amc' => 0,
                    'buffer_stock' => 0,
                    'reorder_level' => 0
                ];
            }

            // Extract quantities and months
            $quantities = $consumptionsWithMonth->pluck('quantity')->values();
            $months = $consumptionsWithMonth->pluck('month_year')->values();
            
            // Start with the 3 most recent months
            $selectedMonths = [];
            $passedMonths = [];
            $failedMonths = [];
            
            // Initial selection: 3 most recent months
            for ($i = 0; $i < 3; $i++) {
                $selectedMonths[] = [
                    'month' => $months[$i],
                    'quantity' => $quantities[$i]
                ];
            }
            
            $attempt = 1;
            $maxAttempts = 10; // Prevent infinite loops
            
            while ($attempt <= $maxAttempts) {
                // Calculate average of selected months
                $average = collect($selectedMonths)->avg('quantity');
                
                // Check each month's deviation
                $allPassed = true;
                $newPassedMonths = [];
                $newFailedMonths = [];
                
                foreach ($selectedMonths as $monthData) {
                    $quantity = $monthData['quantity'];
                    $deviation = abs($quantity - $average) / $average * 100;
                    
                    if ($deviation <= 70) {
                        // Month passed screening
                        $newPassedMonths[] = $monthData;
                    } else {
                        // Month failed screening
                        $newFailedMonths[] = $monthData;
                        $allPassed = false;
                    }
                }
                
                // Add newly passed months to the global passed list
                foreach ($newPassedMonths as $monthData) {
                    if (!collect($passedMonths)->contains('month', $monthData['month'])) {
                        $passedMonths[] = $monthData;
                    }
                }
                
                // Add newly failed months to the global failed list
                foreach ($newFailedMonths as $monthData) {
                    if (!collect($failedMonths)->contains('month', $monthData['month'])) {
                        $failedMonths[] = $monthData;
                    }
                }
                
                // If all months passed, we're done
                if ($allPassed) {
                    break;
                }
                
                // If we have 3 or more passed months, use them
                if (count($passedMonths) >= 3) {
                    $selectedMonths = array_slice($passedMonths, 0, 3);
                    break;
                }
                
                // Need to reselect months including passed ones
                $newSelection = [];
                
                // First, include all passed months
                foreach ($passedMonths as $monthData) {
                    $newSelection[] = $monthData;
                }
                
                // Then add more months from the original list until we have 3
                $monthIndex = 0;
                while (count($newSelection) < 3 && $monthIndex < count($quantities)) {
                    $monthData = [
                        'month' => $months[$monthIndex],
                        'quantity' => $quantities[$monthIndex]
                    ];
                    
                    // Only add if not already in selection and not in failed months
                    $alreadySelected = collect($newSelection)->contains('month', $monthData['month']);
                    $isFailed = collect($failedMonths)->contains('month', $monthData['month']);
                    
                    if (!$alreadySelected && !$isFailed) {
                        $newSelection[] = $monthData;
                    }
                    
                    $monthIndex++;
                }
                
                // Update selected months for next iteration
                $selectedMonths = $newSelection;
                $attempt++;
            }
            
            // Calculate final metrics
            if (count($selectedMonths) >= 3) {
                $amc = collect($selectedMonths)->avg('quantity');
                $amc = round($amc, 2);
                
                // Find the maximum value from selected months
                $maxAMC = max(array_column($selectedMonths, 'quantity'));
                
                // Calculate buffer stock: (Max AMC - AMC) × 3
                $bufferStock = ($maxAMC - $amc) * 3;
                $bufferStock = round(max(0, $bufferStock), 2);
                
                // Calculate reorder level: (AMC × 3) + Buffer Stock
                $reorderLevel = ($amc * 3) + $bufferStock;
                $reorderLevel = round($reorderLevel, 2);
                
                return [
                    'amc' => $amc,
                    'buffer_stock' => $bufferStock,
                    'reorder_level' => $reorderLevel
                ];
            } else {
                return [
                    'amc' => 0,
                    'buffer_stock' => 0,
                    'reorder_level' => 0
                ];
            }

        } catch (\Exception $e) {
            return [
                'amc' => 0,
                'buffer_stock' => 0,
                'reorder_level' => 0
            ];
        }
    }

    /**
     * Get the inventory structure for frontend
     */
    public function getInventoryStructureAttribute()
    {
        // Get inventory items directly for this product
        $inventoryItems = $this->items;
        
        // Calculate all inventory metrics in one optimized call
        $metrics = $this->calculateInventoryMetrics();
        
        // Calculate the current status based on total quantity and reorder level
        $totalQuantity = $inventoryItems->sum('quantity');
        $reorderLevel = $metrics['reorder_level'];
        
        $status = 'in_stock'; // default
        if ($totalQuantity <= 0) {
            $status = 'out_of_stock';
        } elseif ($reorderLevel > 0 && $totalQuantity <= $reorderLevel) {
            $status = 'reorder_level';
        } elseif ($reorderLevel > 0 && $totalQuantity <= ($reorderLevel + ($reorderLevel * 0.3))) {
            $status = 'low_stock';
        }
        
        return [
            'id' => $this->id,
            'product_id' => $this->id,
            'items' => $inventoryItems, // This will be empty array if no items exist
            'amc' => $metrics['amc'],
            'buffer_stock' => $metrics['buffer_stock'],
            'reorder_level' => $metrics['reorder_level'],
            'status' => $status, // Add calculated status
            'product' => [
                'id' => $this->id,
                'name' => $this->name,
                'category' => [
                    'id' => $this->category->id ?? null,
                    'name' => $this->category->name ?? null
                ],
                'dosage' => [
                    'id' => $this->dosage->id ?? null,
                    'name' => $this->dosage->name ?? null
                ]
            ]
        ];
    }
}
