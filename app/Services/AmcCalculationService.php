<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * AMC (Average Monthly Consumption) Calculation Service
 * 
 * Implements the exact 70% deviation screening formula as specified:
 * 
 * Example: Jan=3000, Feb=2000, Mar=3000, Apr=6000, May=2500, Jun=300
 * Step 1: Take closest 3 months (bottom to top): Jun=300, May=2500, Apr=6000
 * Step 2: Calculate average: (300+2500+6000)/3 = 2933.33
 * Step 3: Screen each month for 70% deviation:
 *         Jun: |300-2933|/2933*100 = 89.7% > 70% ❌ (failed)
 *         May: |2500-2933|/2933*100 = 14.8% ≤ 70% ✅ (passed)
 *         Apr: |6000-2933|/2933*100 = 104.5% > 70% ❌ (failed)
 * Step 4: Reselect 3 months including passed ones: May=2500, Mar=3000, Feb=2000
 * Step 5: Calculate new average: (2500+3000+2000)/3 = 2500
 * Step 6: Screen remaining months:
 *         Mar: |3000-2500|/2500*100 = 20% ≤ 70% ✅ (passed)
 *         Feb: |2000-2500|/2500*100 = 20% ≤ 70% ✅ (passed)
 * Step 7: Final AMC = 2500
 */
class AmcCalculationService
{
    /**
     * Calculate AMC for a specific product at a facility
     * 
     * @param int $facilityId
     * @param int $productId
     * @param string|null $excludeMonth Optional month to exclude (format: Y-m)
     * @return array
     */
    public function calculateAmc($facilityId, $productId, $excludeMonth = null)
    {
        // Get monthly consumption data (last 12 months, excluding zeros)
        $monthlyConsumptions = DB::table('monthly_consumption_items as mci')
            ->join('monthly_consumption_reports as mcr', 'mci.parent_id', '=', 'mcr.id')
            ->select('mci.product_id', 'mci.quantity', 'mcr.month_year')
            ->where('mcr.facility_id', $facilityId)
            ->where('mci.product_id', $productId)
            ->where('mci.quantity', '>', 0) // Exclude zero quantities
            ->orderBy('mcr.month_year', 'desc') // Newest first
            ->limit(12)
            ->get();

        // Convert to array for processing
        $monthsData = $monthlyConsumptions->map(function ($item) {
            return [
                'month' => $item->month_year,
                'consumption' => (float) $item->quantity
            ];
        })->toArray();

        // Exclude current month if not specified
        if ($excludeMonth === null) {
            $excludeMonth = Carbon::now()->format('Y-m');
        }

        // Filter out excluded month
        if ($excludeMonth) {
            $monthsData = array_filter($monthsData, function ($month) use ($excludeMonth) {
                return $month['month'] !== $excludeMonth;
            });
            $monthsData = array_values($monthsData); // Re-index array
        }

        $monthsCount = count($monthsData);

        if ($monthsCount === 0) {
            return [
                'amc' => 0,
                'selectedMonths' => [],
                'totalMonths' => 0,
                'calculation' => 'No consumption data available'
            ];
        }

        // Apply AMC screening logic
        $selectedMonths = [];
        $amc = 0;
        $calculation = '';

        if ($monthsCount >= 3) {
            // Step 1: Take closest 3 months (first 3 in our desc-sorted array)
            $firstThreeMonths = array_slice($monthsData, 0, 3);
            
            // Step 2: Calculate average of first 3 months
            $sum = array_sum(array_column($firstThreeMonths, 'consumption'));
            $average = $sum / 3;
            
            // Step 3: Screen each month for 70% deviation
            $passedMonths = [];
            
            foreach ($firstThreeMonths as $month) {
                $quantity = $month['consumption'];
                $deviation = abs($average - $quantity);
                $percentage = $average > 0 ? ($deviation / $average) * 100 : 0;
                
                if ($percentage <= 70) {
                    $passedMonths[] = $month;
                }
            }
            
            // Step 4: If all 3 months passed, use them
            if (count($passedMonths) === 3) {
                $selectedMonths = $firstThreeMonths;
                $amc = $average;
                $calculation = "All 3 closest months passed 70% screening. AMC = {$amc}";
            } else {
                // Step 5: Reselect 3 months including passed ones
                // Get remaining months to combine with passed ones
                $remainingMonths = array_slice($monthsData, 3);
                
                // Build new group: passed months + enough remaining months to make 3
                $newGroup = $passedMonths;
                $remainingIndex = 0;
                
                // Add months from remaining until we have 3 total
                while (count($newGroup) < 3 && $remainingIndex < count($remainingMonths)) {
                    $newGroup[] = $remainingMonths[$remainingIndex];
                    $remainingIndex++;
                }
                
                // If we still don't have 3, fall back to original 3
                if (count($newGroup) < 3) {
                    $selectedMonths = $firstThreeMonths;
                    $amc = $average;
                    $calculation = "Fallback to first 3 months (insufficient data for reselection). AMC = {$amc}";
                } else {
                    // Calculate new average
                    $newSum = array_sum(array_column($newGroup, 'consumption'));
                    $newAverage = $newSum / 3;
                    
                    // Screen the new group - only screen the newly added months
                    // (passed months are already verified)
                    $allValid = true;
                    $passedCount = count($passedMonths);
                    
                    for ($i = $passedCount; $i < count($newGroup); $i++) {
                        $month = $newGroup[$i];
                        $quantity = $month['consumption'];
                        $deviation = abs($newAverage - $quantity);
                        $percentage = $newAverage > 0 ? ($deviation / $newAverage) * 100 : 0;
                        
                        if ($percentage > 70) {
                            $allValid = false;
                            break;
                        }
                    }
                    
                    if ($allValid) {
                        $selectedMonths = $newGroup;
                        $amc = $newAverage;
                        $calculation = "Reselected group with {$passedCount} passed months + " . (3 - $passedCount) . " additional months. AMC = {$amc}";
                    } else {
                        // Final fallback to first 3 months
                        $selectedMonths = $firstThreeMonths;
                        $amc = $average;
                        $calculation = "Reselected group failed screening, using first 3 months. AMC = {$amc}";
                    }
                }
            }
        } elseif ($monthsCount === 2) {
            // If only 2 months available, use both
            $selectedMonths = $monthsData;
            $sum = array_sum(array_column($selectedMonths, 'consumption'));
            $amc = $sum / 2;
            $calculation = "Only 2 months available, no screening applied. AMC = {$amc}";
        } elseif ($monthsCount === 1) {
            // If only 1 month available, use it
            $selectedMonths = $monthsData;
            $amc = $selectedMonths[0]['consumption'];
            $calculation = "Only 1 month available, no screening applied. AMC = {$amc}";
        }

        return [
            'amc' => round($amc, 2),
            'selectedMonths' => $selectedMonths,
            'totalMonths' => $monthsCount,
            'calculation' => $calculation
        ];
    }

    /**
     * Calculate AMC for multiple products at a facility (optimized with batch queries)
     * 
     * @param int $facilityId
     * @param array $productIds
     * @param string|null $excludeMonth
     * @return array
     */
    public function calculateAmcForProducts($facilityId, array $productIds, $excludeMonth = null)
    {
        if (empty($productIds)) {
            return [];
        }

        // Exclude current month if not specified
        if ($excludeMonth === null) {
            $excludeMonth = Carbon::now()->format('Y-m');
        }

        // Calculate date range for last 13 months (12 + 1 for current month exclusion)
        $startDate = Carbon::now()->subMonths(13)->format('Y-m');
        
        // Get all consumption data for all products in one query
        $allConsumptions = DB::table('monthly_consumption_items as mci')
            ->join('monthly_consumption_reports as mcr', 'mci.parent_id', '=', 'mcr.id')
            ->select('mci.product_id', 'mci.quantity', 'mcr.month_year')
            ->where('mcr.facility_id', $facilityId)
            ->whereIn('mci.product_id', $productIds)
            ->where('mci.quantity', '>', 0) // Exclude zero quantities
            ->where('mcr.month_year', '>=', $startDate) // Limit to last 13 months
            ->when($excludeMonth, function ($query, $excludeMonth) {
                return $query->where('mcr.month_year', '!=', $excludeMonth);
            })
            ->orderBy('mci.product_id')
            ->orderBy('mcr.month_year', 'desc') // Newest first
            ->get()
            ->groupBy('product_id');

        $results = [];
        
        // Process each product's data
        foreach ($productIds as $productId) {
            $productConsumptions = $allConsumptions->get($productId, collect());
            
            // Limit to last 12 months and convert to array
            $monthsData = $productConsumptions->take(12)->map(function ($item) {
                return [
                    'month' => $item->month_year,
                    'consumption' => (float) $item->quantity
                ];
            })->toArray();

            $results[$productId] = $this->processAmcCalculation($monthsData, $productId);
        }
        
        return $results;
    }

    /**
     * Process AMC calculation for a single product's consumption data
     * 
     * @param array $monthsData
     * @param int $productId
     * @return array
     */
    private function processAmcCalculation($monthsData, $productId)
    {
        $monthsCount = count($monthsData);

        if ($monthsCount === 0) {
            return [
                'amc' => 0,
                'selectedMonths' => [],
                'totalMonths' => 0,
                'calculation' => 'No consumption data available'
            ];
        }

        // Apply AMC screening logic
        $selectedMonths = [];
        $amc = 0;
        $calculation = '';

        if ($monthsCount >= 3) {
            // Step 1: Take closest 3 months (first 3 in our desc-sorted array)
            $firstThreeMonths = array_slice($monthsData, 0, 3);
            
            // Step 2: Calculate average of first 3 months
            $sum = array_sum(array_column($firstThreeMonths, 'consumption'));
            $average = $sum / 3;
            
            // Step 3: Screen each month for 70% deviation
            $passedMonths = [];
            
            foreach ($firstThreeMonths as $month) {
                $quantity = $month['consumption'];
                $deviation = abs($average - $quantity);
                $percentage = $average > 0 ? ($deviation / $average) * 100 : 0;
                
                if ($percentage <= 70) {
                    $passedMonths[] = $month;
                }
            }
            
            // Step 4: If all 3 months passed, use them
            if (count($passedMonths) === 3) {
                $selectedMonths = $firstThreeMonths;
                $amc = $average;
                $calculation = "All 3 closest months passed 70% screening. AMC = {$amc}";
            } else {
                // Step 5: Reselect 3 months including passed ones
                // Get remaining months to combine with passed ones
                $remainingMonths = array_slice($monthsData, 3);
                
                // Build new group: passed months + enough remaining months to make 3
                $newGroup = $passedMonths;
                $remainingIndex = 0;
                
                // Add months from remaining until we have 3 total
                while (count($newGroup) < 3 && $remainingIndex < count($remainingMonths)) {
                    $newGroup[] = $remainingMonths[$remainingIndex];
                    $remainingIndex++;
                }
                
                // If we still don't have 3, fall back to original 3
                if (count($newGroup) < 3) {
                    $selectedMonths = $firstThreeMonths;
                    $amc = $average;
                    $calculation = "Fallback to first 3 months (insufficient data for reselection). AMC = {$amc}";
                } else {
                    // Calculate new average
                    $newSum = array_sum(array_column($newGroup, 'consumption'));
                    $newAverage = $newSum / 3;
                    
                    // Screen the new group - only screen the newly added months
                    // (passed months are already verified)
                    $allValid = true;
                    $passedCount = count($passedMonths);
                    
                    for ($i = $passedCount; $i < count($newGroup); $i++) {
                        $month = $newGroup[$i];
                        $quantity = $month['consumption'];
                        $deviation = abs($newAverage - $quantity);
                        $percentage = $newAverage > 0 ? ($deviation / $newAverage) * 100 : 0;
                        
                        if ($percentage > 70) {
                            $allValid = false;
                            break;
                        }
                    }
                    
                    if ($allValid) {
                        $selectedMonths = $newGroup;
                        $amc = $newAverage;
                        $calculation = "Reselected group with {$passedCount} passed months + " . (3 - $passedCount) . " additional months. AMC = {$amc}";
                    } else {
                        // Final fallback to first 3 months
                        $selectedMonths = $firstThreeMonths;
                        $amc = $average;
                        $calculation = "Reselected group failed screening, using first 3 months. AMC = {$amc}";
                    }
                }
            }
        } elseif ($monthsCount === 2) {
            // If only 2 months available, use both
            $selectedMonths = $monthsData;
            $sum = array_sum(array_column($selectedMonths, 'consumption'));
            $amc = $sum / 2;
            $calculation = "Only 2 months available, no screening applied. AMC = {$amc}";
        } elseif ($monthsCount === 1) {
            // If only 1 month available, use it
            $selectedMonths = $monthsData;
            $amc = $selectedMonths[0]['consumption'];
            $calculation = "Only 1 month available, no screening applied. AMC = {$amc}";
        }

        return [
            'amc' => round($amc, 2),
            'selectedMonths' => $selectedMonths,
            'totalMonths' => $monthsCount,
            'calculation' => $calculation
        ];
    }
}
