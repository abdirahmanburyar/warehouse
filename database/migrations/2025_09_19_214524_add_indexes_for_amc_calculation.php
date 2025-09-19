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
        Schema::table('monthly_consumption_items', function (Blueprint $table) {
            // Add composite index for AMC calculation query optimization
            // This covers: WHERE product_id IN (...) AND quantity > 0
            $table->index(['product_id', 'quantity'], 'idx_mci_product_quantity');
        });

        Schema::table('monthly_consumption_reports', function (Blueprint $table) {
            // Add composite index for facility and month filtering
            // This covers: WHERE facility_id = ? AND month_year != ?
            $table->index(['facility_id', 'month_year'], 'idx_mcr_facility_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_consumption_items', function (Blueprint $table) {
            $table->dropIndex('idx_mci_product_quantity');
        });

        Schema::table('monthly_consumption_reports', function (Blueprint $table) {
            $table->dropIndex('idx_mcr_facility_month');
        });
    }
};