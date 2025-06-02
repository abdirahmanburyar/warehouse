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
        Schema::create('monthly_inventory_reports', function (Blueprint $table) {
            $table->id();
            $table->string('month_year')->unique(); // Format: YYYY-MM
            $table->decimal('beginning_balance', 10, 2)->default(0);
            $table->decimal('stock_received', 10, 2)->default(0);
            $table->decimal('stock_issued', 10, 2)->default(0);
            $table->decimal('negative_adjustment', 10, 2)->default(0);
            $table->decimal('positive_adjustment', 10, 2)->default(0);
            $table->decimal('closing_balance', 10, 2)->default(0);
            $table->timestamp('generated_at');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_inventory_reports');
    }
};
