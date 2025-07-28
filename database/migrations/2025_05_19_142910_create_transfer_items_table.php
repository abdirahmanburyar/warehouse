p<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Transfer;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Transfer::class)->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->nullable()->cascadeOnDelete();
            $table->integer('quantity'); // This stores the needed quantity
            $table->integer('quantity_on_order')->default(0);
            $table->integer('quantity_to_release')->default(0);
            $table->integer('quantity_per_unit')->default(0);            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_items');
    }
};
