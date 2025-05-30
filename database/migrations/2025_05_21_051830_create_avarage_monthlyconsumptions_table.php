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
        Schema::create('monthly_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('month_year');
            $table->integer('amc')->default(0);
            $table->integer('quantity')->default(0)->comment('Actual consumption quantity for this month');
            $table->timestamps();
            
            // Create a unique index to ensure we only have one record per facility/product/month-year combination
            $table->unique(['facility_id', 'product_id', 'month_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_consumptions');
    }
};
