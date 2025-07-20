<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrder;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PurchaseOrder::class, 'purchase_order_id')->onDelete('cascade');

            $table->foreignIdFor(Product::class, 'product_id')->onDelete('restrict');
            $table->integer('quantity')->default(0);
            $table->string('uom');
            $table->string('original_uom')->nullable();
            $table->integer('original_quantity')->nullable(); 
            $table->foreignIdFor(User::class, 'edited_by')->nullable(); 
            $table->double('unit_cost')->default(0);
            $table->double('total_cost')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints first
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('purchase_order_items');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
