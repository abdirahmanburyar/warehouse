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
        if (!Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $table) {
                $table->id();
                $table->string('asset_number')->unique(); // Asset number (e.g., ASSET-001)
                $table->date('acquisition_date'); // When the asset was acquired
                $table->foreignId('fund_source_id')->constrained('fund_sources');
                $table->foreignId('region_id')->constrained('regions');
                $table->foreignId('asset_location_id')->constrained('asset_locations');
                $table->foreignId('sub_location_id')->constrained('sub_locations');

                // approvals
                $table->foreignId('submitted_by')->constrained('users');
                $table->timestamp('submitted_at');

                $table->foreignId('reviewed_by')->constrained('users')->nullable();
                $table->timestamp('reviewed_at')->nullable();

                $table->foreignId('approved_by')->constrained('users')->nullable();
                $table->timestamp('approved_at')->nullable();

                $table->foreignId('rejected_by')->constrained('users')->nullable();
                $table->timestamp('rejected_at')->nullable();

                $table->text('rejection_reason')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Modify existing table to ensure nullable fields
            Schema::table('assets', function (Blueprint $table) {
                // Change submitted_at from date to timestamp if it exists
                if (Schema::hasColumn('assets', 'submitted_at')) {
                    $table->timestamp('submitted_at')->change();
                }
                
                // Ensure reviewed_by is nullable
                if (Schema::hasColumn('assets', 'reviewed_by')) {
                    $table->foreignId('reviewed_by')->nullable()->change();
                }
                
                // Ensure reviewed_at is timestamp and nullable
                if (Schema::hasColumn('assets', 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable()->change();
                }
                
                // Ensure approved_by is nullable
                if (Schema::hasColumn('assets', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->change();
                }
                
                // Ensure approved_at is timestamp and nullable
                if (Schema::hasColumn('assets', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->change();
                }
                
                // Ensure rejected_by is nullable
                if (Schema::hasColumn('assets', 'rejected_by')) {
                    $table->foreignId('rejected_by')->nullable()->change();
                }
                
                // Ensure rejected_at is timestamp and nullable
                if (Schema::hasColumn('assets', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('assets');
        Schema::enableForeignKeyConstraints();
    }
};
