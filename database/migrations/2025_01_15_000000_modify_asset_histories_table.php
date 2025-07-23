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
        Schema::table('asset_histories', function (Blueprint $table) {
            // Drop existing columns that don't match our workflow
            $table->dropColumn(['field_name', 'transfer_date', 'changed_by']);
            
            // Add new columns to match our approval workflow
            $table->string('action')->after('asset_id'); // 'reviewed', 'approved', 'rejected', etc.
            $table->string('action_type')->after('action'); // 'approval', 'transfer', 'retirement', 'status_change'
            $table->json('old_value')->nullable()->change(); // Change to JSON for complex data
            $table->json('new_value')->change(); // Change to JSON for complex data
            $table->foreignId('performed_by')->constrained('users')->after('new_value'); // Who performed the action
            $table->timestamp('performed_at')->after('performed_by'); // When the action was performed
            $table->foreignId('approval_id')->nullable()->constrained('asset_approvals')->after('performed_at'); // Link to approval record
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_histories', function (Blueprint $table) {
            // Revert the changes
            $table->dropForeign(['performed_by', 'approval_id']);
            $table->dropColumn(['action', 'action_type', 'performed_by', 'performed_at', 'approval_id']);
            
            // Restore original columns
            $table->string('field_name')->after('asset_id');
            $table->date('transfer_date')->after('new_value');
            $table->foreignId('changed_by')->constrained('users')->after('transfer_date');
            
            // Revert data types
            $table->string('old_value')->nullable()->change();
            $table->string('new_value')->change();
        });
    }
}; 