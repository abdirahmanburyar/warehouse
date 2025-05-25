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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('district_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
            // Add unique constraint for name within a district
            $table->unique(['name', 'district_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
