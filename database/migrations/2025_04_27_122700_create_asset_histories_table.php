<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // 'reviewed', 'approved', 'rejected', 'transfer_reviewed', etc.
            $table->string('action_type'); // 'approval', 'transfer', 'retirement', 'status_change'
            $table->json('old_value')->nullable(); // Previous state (can be complex data)
            $table->json('new_value'); // New state (can be complex data)
            $table->text('notes')->nullable(); // Additional notes or comments
            $table->foreignId('performed_by')->constrained('users'); // Who performed the action
            $table->timestamp('performed_at'); // When the action was performed
            $table->foreignId('approval_id')->nullable()->constrained('asset_approvals'); // Link to approval record
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_histories');
    }
};
