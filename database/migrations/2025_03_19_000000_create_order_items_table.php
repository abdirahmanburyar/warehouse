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
            $table->integer('quantity'); // This stores the needed quantity
            $table->integer('quantity_on_order')->default(0);
            $table->integer('soh')->default(0);
            $table->double('amc')->default(0);
            $table->string('uom')->nullable();
            $table->integer('quantity_to_release')->default(0);
            $table->integer('no_of_days')->default(0);
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
