<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    public function up()
    {
        Schema::create('inventory_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('transfer_item_id')->nullable()->constrained('transfer_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses');
            $table->string('location')->nullable();
            $table->string('batch_number');
            $table->string('uom')->nullable();
            $table->string('barcode')->nullable();
            $table->date('expiry_date');
            $table->integer('allocated_quantity');
            $table->string('transfer_reason')->nullable();
            $table->double('unit_cost')->nullable();
            $table->double('total_cost')->nullable();
            $table->string('allocation_type'); // quarterly, emergency, etc.
            $table->text('notes')->nullable();
            $table->integer('update_quantity')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        // allow me to ignore restricts
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('inventory_allocations');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
