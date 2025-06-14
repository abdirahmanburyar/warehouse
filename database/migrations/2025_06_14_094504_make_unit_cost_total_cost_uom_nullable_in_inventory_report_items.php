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
        Schema::table('inventory_report_items', function (Blueprint $table) {
            $table->string('uom')->nullable()->change();
            $table->decimal('unit_cost', 10, 2)->nullable()->change();
            $table->decimal('total_cost', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_report_items', function (Blueprint $table) {
            $table->string('uom')->nullable(false)->change();
            $table->decimal('unit_cost', 10, 2)->nullable(false)->change();
            $table->decimal('total_cost', 15, 2)->nullable(false)->change();
        });
    }
};
