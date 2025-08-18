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
        Schema::create('received_quantities', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('transfer_id')->nullable()->constrained('transfers')->nullOnDelete();
            $table->foreignId('packing_list_id')->nullable()->constrained('packing_lists')->nullOnDelete();
            $table->date('expiry_date')->nullable();
            $table->string('uom')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('barcode')->nullable();
            $table->string('batch_number')->nullable();
            $table->double('unit_cost')->nullable();
            $table->string('source')->nullable();
            $table->double('total_cost')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('received_quantities');
    }
};
