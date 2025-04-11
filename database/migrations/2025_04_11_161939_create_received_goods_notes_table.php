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
        Schema::create('received_goods_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_id')->constrained()->cascadeOnDelete();
            $table->string('rgn_number')->unique();
            $table->foreignId('receiver_id')->constrained('users');
            $table->foreignId('warehouse_id')->constrained();
            $table->text('receiving_notes')->nullable();
            $table->string('supplier_vehicle_number')->nullable();
            $table->string('supplier_driver_name')->nullable();
            $table->string('supplier_driver_phone')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('status')->default('pending'); // pending, received
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('received_goods_notes');
    }
};
