<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
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
            $table->foreignIdFor(Facility::class)->cascadeOnDelete();
            $table->foreignIdFor(User::class)->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->string('order_type');
            $table->enum('status', ['pending', 'approved','rejected', 'in_process', 'dispatched', 'delivered', 'received'])->default('pending');
            $table->dateTime('order_date');
            $table->date('expected_date')->nullable();
            $table->foreignIdFor(User::class, 'approved_by')->nullable();
            $table->foreignIdFor(User::class, 'rejected_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->foreignIdFor(User::class, 'dispatched_by')->nullable();
            $table->dateTime('dispatched_at')->nullable();
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
