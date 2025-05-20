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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transferID')->unique()->nullable();            
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('from_facility_id')->nullable()->constrained('facilities')->onDelete('cascade');
            $table->foreignId('to_facility_id')->nullable()->constrained('facilities')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('quantity');
            $table->date('transfer_date');
            $table->string('status')->default('pending'); // pending, in_process, dispatched, approved, rejected, transfered
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
