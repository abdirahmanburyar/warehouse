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
        Schema::create('issue_quantity_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->foreignId('parent_id')->nullable()->constrained('issue_quantity_reports');            
            $table->foreignId('warehouse_id');
            $table->integer('quantity');
            $table->string('batch_number');
            $table->string('uom')->nullable();
            $table->date('expiry_date');
            $table->double('unit_cost');
            $table->double('total_cost');
            $table->date('issued_date');
            $table->string('barcode')->nullable();
            $table->foreignId('issued_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_quantity_items');
    }
};
