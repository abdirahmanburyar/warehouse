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
        Schema::create('facility_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('facility_inventory_id')->constrained()->onDelete('cascade');
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->integer('quantity');
            $table->double('unit_cost');
            $table->double('total_cost');
            $table->string('barcode')->nullable();
            $table->string('uom')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_inventory_items');
    }
};
