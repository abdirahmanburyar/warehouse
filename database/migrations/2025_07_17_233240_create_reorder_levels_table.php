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
        Schema::create('reorder_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->decimal('amc', 10, 2)->default(0); // Annual Monthly Consumption
            $table->integer('lead_time')->default(5); // Default 150 days / 30 = 5
            $table->decimal('reorder_level', 10, 2)->default(0); // Calculated field: AMC * Lead Time
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reorder_levels');
    }
};
