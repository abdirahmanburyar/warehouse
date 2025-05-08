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
        // Schema::create('packing_lists', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('packing_list_number')->unique();
        //     $table->foreignId('purchase_order_id')->constrained();
        //     $table->foreignId('product_id')->constrained();
        //     $table->foreignId('warehouse_id')->constrained();
        //     $table->date('expire_date')->nullable();
        //     $table->string('batch_number')->nullable();
        //     $table->string('location')->nullable();
        //     $table->integer('quantity');
        //     $table->double('unit_cost');
        //     $table->double('total_cost');
        //     $table->timestamps();
        // });

        Schema::create('packing_list_differences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->enum('status', ['Expired', 'Damaged','Missing']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     Schema::dropIfExists('packing_list_differences');
    //     Schema::dropIfExists('packing_lists');
    // }
};
