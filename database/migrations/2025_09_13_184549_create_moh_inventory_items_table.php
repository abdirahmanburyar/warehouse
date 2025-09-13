<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('moh_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moh_inventory_id')->constrained('moh_inventories')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->integer('quantity')->default(0);
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('barcode')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->string('uom')->nullable();
            $table->string('source')->nullable();
            $table->double('unit_cost')->nullable();
            $table->double('total_cost')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moh_inventory_items');
    }
};
