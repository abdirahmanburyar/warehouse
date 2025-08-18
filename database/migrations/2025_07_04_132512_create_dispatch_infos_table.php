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
        Schema::create('dispatch_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('transfer_id')->nullable()->constrained('transfers')->nullOnDelete();
            $table->foreignId('logistic_company_id')->nullable()->constrained('logistic_companies')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->date('dispatch_date')->nullable();
            $table->integer('no_of_cartoons')->nullable();
            $table->integer('received_cartons')->nullable();
            $table->string('driver_number')->nullable();
            $table->string('plate_number')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_infos');
    }
};
