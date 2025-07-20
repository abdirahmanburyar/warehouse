<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix auto-increment sequence for purchase_order_items table
        DB::statement('ALTER TABLE purchase_order_items AUTO_INCREMENT = (SELECT COALESCE(MAX(id), 0) + 1 FROM purchase_order_items)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this fix
    }
}; 