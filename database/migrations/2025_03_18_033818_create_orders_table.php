<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Facility;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Warehouse::class)->cascadeOnDelete();
            $table->foreignIdFor(Facility::class)->cascadeOnDelete();
            $table->foreignIdFor(User::class)->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->enum('status', ['pending', 'approved', 'rejected', 'in processing', 'dispatched', 'delivered'])->default('pending');
            $table->integer('number_items');
            $table->double('total_amount');
            $table->text('notes')->nullable();
            $table->dateTime('order_date');
            $table->dateTime('expected_date')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('in_processing_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('orders');
        Schema::enableForeignKeyConstraints();
    }
};
