<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Validation\Rule;

class PurchaseOrderItemsImport implements ToCollection, WithHeadingRow, WithValidation
{
    use Importable;

    protected $purchaseOrderId;

    public function __construct($purchaseOrderId)
    {
        $this->purchaseOrderId = $purchaseOrderId;
    }

    public function rules(): array
    {
        return [
            'item_description' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'total_cost' => ['required', 'numeric', 'min:0'],
            'uom' => ['required', 'string', 'max:50'],
            '*.item_description' => ['required', 'string', 'max:255'],
            '*.quantity' => ['required', 'numeric', 'min:0'],
            '*.unit_cost' => ['required', 'numeric', 'min:0'],
            '*.total_cost' => ['required', 'numeric', 'min:0'],
            '*.uom' => ['required', 'string', 'max:50'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'item_description.required' => 'The item description is required',
            'quantity.required' => 'The quantity is required',
            'unit_cost.required' => 'The unit cost is required',
            'total_cost.required' => 'The total cost is required',
            'uom.required' => 'The unit of measure (UOM) is required',
            'quantity.numeric' => 'The quantity must be a number',
            'unit_cost.numeric' => 'The unit cost must be a number',
            'total_cost.numeric' => 'The total cost must be a number',
        ];
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('The Excel file is empty');
        }

        // Validate required columns
        $requiredColumns = ['item_description', 'quantity', 'unit_cost', 'total_cost', 'uom'];
        $missingColumns = array_diff($requiredColumns, array_keys($rows->first()->toArray()));
        if (!empty($missingColumns)) {
            throw new \Exception('Missing required columns: ' . implode(', ', $missingColumns));
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                if (empty($row['item_description'])) {
                    continue;
                }

                $item_description = trim($row['item_description']);
                $uom = trim($row['uom']);
                $quantity = floatval($row['quantity']);
                $unit_cost = floatval($row['unit_cost']);
                
                // Calculate total cost instead of using Excel value
                $total_cost = $quantity * $unit_cost;

                // Extract category from the row
                $category = Category::firstOrCreate(
                    ['name' => $row['category'] ?? 'Drug'],
                    ['status' => 'active']
                );

                // Extract dosage form and create if not exists
                $dosageForm = Dosage::firstOrCreate(
                    ['name' => $row['dosage_form'] ?? 'Tablet'],
                    ['status' => 'active']
                );

                // Find or create product by name with category and dosage
                $product = Product::firstOrCreate(
                    ['name' => $item_description],
                    [
                        'status' => 'active',
                        'category_id' => $category->id,
                        'dosage_id' => $dosageForm->id
                    ]
                );

                // Create new item for each row
                $poi = PurchaseOrderItem::create([
                    'purchase_order_id' => $this->purchaseOrderId,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unit_cost,
                    'total_cost' => $total_cost,
                    'uom' => $uom
                ]);
                logger()->info($poi);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing items:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
