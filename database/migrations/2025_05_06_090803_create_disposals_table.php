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
            $table->string('disposal_id')->unique();
            $table->foreignId('disposed_by')->nullable()->nullOnDelete();
            $table->string('facility')->nullable();
            $table->string('warehouse')->nullable();
            $table->foreignId('transfer_id')->nullable()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->unique()->nullOnDelete();
            $table->foreignId('packing_list_id')->nullable()->unique()->nullOnDelete();
            $table->timestamp('disposed_at');
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
        Schema::dropIfExists('disposals');
        Schema::enableForeignKeyConstraints();
    }
};
