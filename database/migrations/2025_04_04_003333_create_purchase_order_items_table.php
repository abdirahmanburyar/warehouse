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
            $table->foreignId('purchase_order_id')->onDelete('cascade');
            $table->foreignId('packing_list_id')->onDelete('cascade');

            $table->foreignId('product_id')->onDelete('restrict');
            $table->foreignId('warehouse_id')->onDelete('restrict');
            $table->integer('quantity');
            $table->string('location');
            $table->integer('received_quantity')->default(0);

            $table->double('unit_cost')->default(0);
            $table->double('total_cost')->default(0);

            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('generic_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
