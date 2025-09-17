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
        Schema::create('inventory_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_report_id')->constrained('inventory_reports')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->integer('beginning_balance')->default(0);
            $table->integer('received_quantity')->default(0);
            $table->integer('issued_quantity')->default(0);
            $table->integer('other_quantity_out')->default(0);
            $table->integer('positive_adjustment')->default(0);
            $table->integer('negative_adjustment')->default(0);
            $table->integer('closing_balance')->default(0);
            $table->integer('total_closing_balance')->default(0);
            $table->integer('average_monthly_consumption')->default(0);
            $table->string('months_of_stock')->nullable();
            $table->integer('quantity_in_pipeline')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_report_items');
    }
};
