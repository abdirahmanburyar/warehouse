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
        Schema::create('received_backorders', function (Blueprint $table) {
            $table->id();
            $table->string('received_backorder_number')->unique();
            $table->foreignId('product_id')->nullable()->nullOnDelete();
            $table->foreignId('received_by')->nullable()->nullOnDelete();
            $table->string('barcode')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('uom')->nullable();
            $table->date('received_at');
            $table->integer('quantity');
            $table->string('status');
            $table->string('type');
            $table->string('location')->nullable();
            $table->string('facility')->nullable();
            $table->string('warehouse')->nullable();
            $table->double('unit_cost')->nullable();
            $table->double('total_cost')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('attachments')->nullable();
            
            // Additional fields for back order integration
            $table->string('back_order_id')->nullable();
            $table->string('packing_list_id')->nullable();
            $table->string('packing_list_number')->nullable();
            $table->string('purchase_order_id')->nullable();
            $table->string('purchase_order_number')->nullable();
            $table->string('supplier_id')->nullable();
            $table->string('supplier_name')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('received_backorders');
    }
};
