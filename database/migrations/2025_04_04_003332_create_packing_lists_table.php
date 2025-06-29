<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PurchaseOrderItem;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packing_lists', function (Blueprint $table) {
            $table->id();
            $table->string('packing_list_number');
            $table->foreignId('purchase_order_id')->constrained();
            $table->string('status')->default('pending');            
            $table->string('notes')->nullable();            
            $table->string('ref_no')->nullable();
            $table->timestamp('pk_date')->default(now());
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->timestamp('confirmed_at')->nullable()->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_list_differences');

    }
};
