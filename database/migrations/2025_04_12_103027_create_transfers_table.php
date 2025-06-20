<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transferID')->unique()->nullable();            
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('from_facility_id')->nullable()->constrained('facilities')->onDelete('cascade');
            $table->foreignId('to_facility_id')->nullable()->constrained('facilities')->onDelete('cascade');
            $table->foreignIdFor(User::class, 'created_by')->cascadeOnDelete();
            $table->enum('status', ['pending', 'reviewed', 'approved','rejected', 'in_process', 'dispatched', 'delivered', 'received'])->default('pending');
            $table->foreignIdFor(User::class, 'approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->foreignIdFor(User::class, 'rejected_by')->nullable();
            $table->dateTime('rejected_at')->nullable();

            $table->foreignIdFor(User::class, 'dispatched_by')->nullable();
            $table->dateTime('dispatched_at')->nullable();

            $table->foreignIdFor(User::class, 'delivered_by')->nullable();
            $table->dateTime('delivered_at')->nullable();

            $table->foreignIdFor(User::class, 'received_by')->nullable();
            $table->dateTime('received_at')->nullable();

            $table->foreignIdFor(User::class, 'reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();

            $table->foreignIdFor(User::class, 'processed_by')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->date('transfer_date');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};