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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('serial_number')->unique();
            $table->string('category');
            $table->string('custody')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('location');
            $table->double('purchase_cost')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('transfer_date')->nullable();
            $table->enum('status', ['active', 'in_use', 'maintenance', 'retired', 'damaged'])->default('active');
            $table->text('notes')->nullable();
            $table->string('current_custodian')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
