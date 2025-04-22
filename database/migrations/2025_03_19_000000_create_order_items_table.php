<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->nullable()->cascadeOnDelete();
            $table->foreignIdFor(Warehouse::class)->nullable()->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('quantity_on_order')->default(0);
            $table->foreignId('reviewed_by')->nullable();
            $table->date('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->date('approved_at')->nullable();
            $table->foreignId('dispatched_by')->nullable();
            $table->date('dispatched_at')->nullable();
            $table->boolean('in_process')->default(false);
            $table->boolean('delivered')->default(false);
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('order_items');
        Schema::enableForeignKeyConstraints();
    }
};
