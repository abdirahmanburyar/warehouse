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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            
            // Pharmaceutical specific attributes
            $table->string('active_ingredient')->nullable();
            $table->string('dosage_form')->nullable(); // tablet, capsule, syrup, etc.
            $table->string('strength')->nullable(); // e.g., 500mg, 10mg/ml
            $table->string('manufacturer')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('storage_conditions')->nullable(); // e.g., "Store below 25°C"
            $table->string('prescription_required')->default('no'); // yes/no
            $table->string('regulatory_status')->nullable(); // approved, under review, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
