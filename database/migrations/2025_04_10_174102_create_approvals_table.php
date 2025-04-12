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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            // Polymorphic relationship for the model being approved
            $table->string('model');
            
            // The role required to perform this approval action
            $table->foreignId('role_id')->constrained('roles');
            
            // The type of approval action (verify, confirm, approve)
            $table->string('action');
            
            // The order in which approvals should happen
            $table->integer('sequence')->default(1);
            
            // Status of this approval step
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Who performed the approval action
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            
            // Comments or notes for this approval step
            $table->text('notes')->nullable();
            
            // Tracking who created/updated this approval step
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Custom name for unique constraint to avoid MySQL identifier length limit
            $table->unique(['model', 'role_id', 'action', 'sequence'], 'approval_step_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
