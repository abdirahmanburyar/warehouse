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
        Schema::create('monthly_quantity_receiveds', function (Blueprint $table) {
            $table->id();
            $table->string('month_year');
            $table->integer('total_quantity')->default(0);
            $table->string('generated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_quantity_receiveds');
    }
};
