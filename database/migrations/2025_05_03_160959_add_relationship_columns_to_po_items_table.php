<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('po_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('dosage_id')->nullable()->constrained('dosages')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('po_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['dosage_id']);
            $table->dropColumn(['product_id', 'category_id', 'dosage_id']);
        });
    }
};
