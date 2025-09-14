<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing UOM data loading...\n";

// Test 1: Check if there are any inventory items with UOM
$inventoryItems = App\Models\InventoryItem::select('id', 'product_id', 'uom', 'unit_cost', 'total_cost')->get();
echo "Total inventory items: " . $inventoryItems->count() . "\n";

foreach ($inventoryItems as $item) {
    echo "ID: " . $item->id . 
         ", Product ID: " . $item->product_id . 
         ", UOM: " . ($item->uom ?? 'NULL') . 
         ", Unit Cost: " . ($item->unit_cost ?? 'NULL') . 
         ", Total Cost: " . ($item->total_cost ?? 'NULL') . "\n";
}

echo "\nTesting Product with items relationship...\n";

// Test 2: Check if products are loading items with UOM
$products = App\Models\Product::with(['items' => function($query) {
    $query->select('id', 'product_id', 'warehouse_id', 'quantity', 'location', 'batch_number', 'expiry_date', 'uom', 'unit_cost', 'total_cost');
}])->limit(3)->get();

echo "Products with items: " . $products->count() . "\n";

foreach ($products as $product) {
    echo "Product: " . $product->name . "\n";
    echo "Items count: " . $product->items->count() . "\n";
    foreach ($product->items as $item) {
        echo "  - Item ID: " . $item->id . 
             ", UOM: " . ($item->uom ?? 'NULL') . 
             ", Unit Cost: " . ($item->unit_cost ?? 'NULL') . 
             ", Total Cost: " . ($item->total_cost ?? 'NULL') . "\n";
    }
    echo "\n";
}
