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
        Schema::create('liquidates', function (Blueprint $table) {
            $table->id();
            $table->string('liquidate_id')->unique();
            $table->foreignId('liquidated_by')->nullable()->nullOnDelete();
            $table->foreignId('transfer_id')->nullable()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->unique()->nullOnDelete();
            $table->foreignId('packing_list_id')->nullable()->unique()->nullOnDelete();
            $table->string('facility')->nullable();
            $table->string('warehouse')->nullable();
            $table->timestamp('liquidated_at');
            $table->string('status');
            $table->string('source')->nullable();
            $table->foreignId('reviewed_by')->nullable()->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('back_order_id')->nullable()->unique()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('liquidates');
        Schema::enableForeignKeyConstraints();
    }
};
