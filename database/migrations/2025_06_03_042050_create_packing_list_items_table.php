<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PurchaseOrderItem;
use App\Models\PackingListItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packing_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_id')->constrained('packing_lists')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignIdFor(PurchaseOrderItem::class, 'po_item_id')->constrained()->onDelete('cascade');
            $table->string('barcode')->nullable();
            $table->string('batch_number')->nullable();        
            $table->string('location');
            $table->integer('quantity');
            $table->string('uom');
            $table->date('expire_date');
            $table->double('unit_cost');
            $table->double('total_cost');
            $table->timestamps();
        });

        Schema::create('packing_list_differences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_list_item_id')->constrained('packing_list_items')->onDelete('cascade');
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('cascade');
            $table->foreignId('transfer_item_id')->nullable()->constrained('transfer_items')->onDelete('cascade');
            $table->foreignId('back_order_id')->nullable()->constrained('back_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->boolean('finalized')->default(false);
            $table->enum('status', ['Expired', 'Damaged','Missing','Low quality','Lost']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_list_differences');
        Schema::dropIfExists('packing_list_items');
    }
};
