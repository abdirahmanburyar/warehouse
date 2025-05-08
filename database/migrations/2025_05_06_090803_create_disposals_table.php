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
        Schema::create('disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->nullOnDelete();
            $table->foreignId('packing_list_id')->nullable()->nullOnDelete();
            $table->foreignId('inventory_id')->nullable()->nullOnDelete();
            $table->foreignId('disposed_by')->nullOnDelete();
            $table->date('disposed_at');
            $table->integer('quantity');
            $table->string('status');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposals');
    }
};
