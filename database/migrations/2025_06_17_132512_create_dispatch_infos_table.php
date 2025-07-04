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
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('transfer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('dispatch_date');
            $table->string('driver_name');
            $table->string('no_of_cartoons');
            $table->string('driver_number');
            $table->string('plate_number');
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
