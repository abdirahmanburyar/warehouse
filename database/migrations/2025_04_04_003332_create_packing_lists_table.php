<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PurchaseOrderItem;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packing_lists', function (Blueprint $table) {
            $table->id();
            $table->string('packing_list_number');
            $table->foreignId('purchase_order_id')->constrained();
            $table->foreignIdFor(PurchaseOrderItem::class, 'po_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->date('expire_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->foreignId('location_id')->nullable();
            $table->integer('quantity');
            $table->double('unit_cost');
            $table->double('total_cost');
            $table->status('status')->default('pending');
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->timestamp('confirmed_at')->nullable()->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

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
    public function down(): void
    {
        Schema::dropIfExists('packing_list_differences');
        Schema::dropIfExists('packing_lists');
    }
};
