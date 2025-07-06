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
        Schema::table('packing_list_differences', function (Blueprint $table) {
            $table->foreignId('back_order_id')->nullable()->constrained('back_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packing_list_differences', function (Blueprint $table) {
            $table->dropForeign(['back_order_id']);
            $table->dropColumn('back_order_id');
        });
    }
};
