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
        Schema::create('disposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposal_id')->constrained('disposals')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->string('barcode')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('uom')->nullable();
            $table->string('location')->nullable();
            $table->string('facility')->nullable();
            $table->string('warehouse')->nullable();
            $table->text('note')->nullable();
            $table->string('type')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposal_items');
    }
};
