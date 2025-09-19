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
        Schema::table('warehouse_amcs', function (Blueprint $table) {
            // Add composite index for warehouse AMC calculation query optimization
            // This covers: WHERE product_id IN (...) AND quantity > 0 AND month_year >= ?
            $table->index(['product_id', 'quantity'], 'idx_wamc_product_quantity');
            $table->index(['product_id', 'month_year'], 'idx_wamc_product_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_amcs', function (Blueprint $table) {
            $table->dropIndex('idx_wamc_product_quantity');
            $table->dropIndex('idx_wamc_product_month');
        });
    }
};