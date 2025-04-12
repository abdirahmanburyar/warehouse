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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('packing_list_id')->nullable()->constrained('packing_lists')->onDelete('cascade');

            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->onDelete('restrict');
            $table->string('location')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('received_quantity')->default(0);
            $table->integer('damage_quantity')->default(0);

            $table->double('unit_cost')->default(0);
            $table->double('total_cost')->default(0);

            $table->date('expiry_date');
            $table->string('batch_number');
            $table->string('generic_name')->nullable();

            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
