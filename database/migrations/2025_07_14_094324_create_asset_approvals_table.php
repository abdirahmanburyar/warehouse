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
        Schema::create('asset_approvals', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship for the asset being approved
            $table->morphs('approvable');
            
            // The role required to perform this approval action
            $table->foreignId('role_id')->constrained('roles');
            
            // The type of approval action (verify, confirm, approve)
            $table->string('action');
            
            // The order in which approvals should happen
            $table->integer('sequence')->default(1);
            
            // Status of this approval step
            $table->enum('status', ['pending', 'reviewed', 'approved', 'rejected'])->default('pending');
            
            // Who performed the approval action
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            
            // Review fields for the review step
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            
            // Comments or notes for this approval step
            $table->text('notes')->nullable();
            
            // JSON data for transfer approvals (stores old/new custodian, dates, etc.)
            $table->json('transfer_data')->nullable();
            
            // Tracking who created/updated this approval step
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index for better performance
            $table->index(['approvable_type', 'approvable_id', 'status']);
            $table->index(['role_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_approvals');
    }
};
