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
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_item_id')->constrained('asset_items')->onDelete('cascade');
            $table->string('maintenance_type'); // Type of maintenance (preventive, corrective, etc.)
            $table->text('description'); // Description of the maintenance work
            $table->date('scheduled_date')->nullable(); // When maintenance is scheduled
            $table->date('completed_date')->nullable(); // When maintenance was completed
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->double('cost')->nullable()->default(0); // Cost of maintenance
            $table->foreignId('performed_by')->nullable()->constrained('assignees')->nullOnDelete(); // Who performed the maintenance
            $table->text('notes')->nullable(); // Additional notes
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance');
    }
};
