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
        Schema::create('back_order_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_id')->constrained('packing_lists')->onDelete('cascade');
            $table->foreignId('transfer_id')->constrained('transfers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->string('status'); // Missing, Damaged, etc.
            $table->text('note')->nullable();
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('back_order_histories');
    }
};
