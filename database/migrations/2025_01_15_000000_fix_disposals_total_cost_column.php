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
        Schema::table('disposals', function (Blueprint $table) {
            // Rename the column from 'tota_cost' to 'total_cost'
            $table->renameColumn('tota_cost', 'total_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disposals', function (Blueprint $table) {
            // Revert the column name back to 'tota_cost'
            $table->renameColumn('total_cost', 'tota_cost');
        });
    }
}; 