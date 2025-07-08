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
        Schema::create('back_orders', function (Blueprint $table) {
            $table->id();
            $table->string('back_order_number')->unique();
            $table->foreignId('packing_list_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('transfer_id')->constrained()->onDelete('cascade');
            $table->date('back_order_date');
            $table->integer('total_items')->default(0);
            // reported by [warehouse_id, facility_id]
            $table->string('reported_by')->nullable();
            $table->integer('total_quantity')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('attach_documents')->nullable();
            $table->text('source_type')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ignore foreign key constraints
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('back_orders');
        Schema::enableForeignKeyConstraints();
    }
};
