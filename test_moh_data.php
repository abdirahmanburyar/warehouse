<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking MOH Inventory Items...\n";

$mohItems = App\Models\MohInventoryItem::with(['product', 'warehouse'])->get();
echo "Total MOH items: " . $mohItems->count() . "\n";

foreach ($mohItems as $item) {
    echo "ID: " . $item->id . 
         ", UOM: " . ($item->uom ?? 'NULL') . 
         ", Product: " . ($item->product->name ?? 'NULL') . 
         ", Unit Cost: " . ($item->unit_cost ?? 'NULL') . 
         ", Total Cost: " . ($item->total_cost ?? 'NULL') . "\n";
}

echo "\nChecking Inventory Items...\n";
$inventoryItems = App\Models\InventoryItem::with(['product', 'warehouse'])->get();
echo "Total Inventory items: " . $inventoryItems->count() . "\n";

foreach ($inventoryItems as $item) {
    echo "ID: " . $item->id . 
         ", UOM: " . ($item->uom ?? 'NULL') . 
         ", Product: " . ($item->product->name ?? 'NULL') . 
         ", Unit Cost: " . ($item->unit_cost ?? 'NULL') . 
         ", Total Cost: " . ($item->total_cost ?? 'NULL') . "\n";
}
