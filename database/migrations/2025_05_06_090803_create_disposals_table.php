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
            $table->foreignId('product_id')->nullable()->nullOnDelete();            
            $table->foreignId('disposed_by')->nullable()->nullOnDelete();
            $table->string('barcode')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('uom')->nullable();
            $table->date('disposed_at');
            $table->integer('quantity');
            $table->string('status');
            $table->string('type');
            $table->string('location')->nullable();
            $table->string('facility')->nullable();
            $table->string('warehouse')->nullable();
            $table->double('unit_cost')->nullable();
            $table->double('tota_cost')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('attachments')->nullable();
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
