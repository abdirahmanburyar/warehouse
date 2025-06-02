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
        Schema::create('inventory_reports', function (Blueprint $table) {
            $table->id();
            $table->string('month_year')->unique();
            $table->string('generated_by');
            $table->string('status')->default('pending');
            $table->foreignId('submitted_by')->nullable()->constrained('users');
            $table->datetime('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->datetime('approved_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->datetime('reviewed_at')->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_reports');
    }
};
