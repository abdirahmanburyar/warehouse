<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('transfer_item_id')->constrained('transfer_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('location_id')->constrained('locations');
            $table->string('batch_number');
            $table->string('uom')->nullable();
            $table->string('barcode')->nullable();
            $table->date('expiry_date');
            $table->integer('allocated_quantity');
            $table->double('unit_cost');
            $table->double('total_cost');
            $table->string('allocation_type')->default('quarterly'); // quarterly, emergency, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_allocations');
    }
};
