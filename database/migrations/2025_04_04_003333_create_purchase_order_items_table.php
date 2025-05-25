<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');

            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->integer('quantity')->default(0);
            $table->string('uom');
            $table->string('original_uom')->nullable();
            $table->string('original_quantity')->nullable(); 
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
        Schema::dropIfExists('purchase_order_items');
    }
};
