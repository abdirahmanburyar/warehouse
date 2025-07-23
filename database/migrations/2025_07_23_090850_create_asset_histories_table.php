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
        Schema::create('asset_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('action'); // 'status_changed', 'transferred', 'retired', 'approved', 'rejected'
            $table->string('action_type'); // 'status_change', 'transfer', 'retirement', 'approval'
            $table->json('old_value')->nullable(); // Store old values
            $table->json('new_value')->nullable(); // Store new values
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users');
            $table->timestamp('performed_at')->nullable();
            $table->foreignId('approval_id')->nullable()->constrained('asset_approvals');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['asset_id', 'action_type']);
            $table->index(['performed_by', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_histories');
    }
};
