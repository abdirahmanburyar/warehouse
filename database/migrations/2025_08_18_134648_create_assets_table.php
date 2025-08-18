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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('tag_no')->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('asset_tag');
            $table->foreignId('asset_category_id');
            $table->foreignId('assignee_id')->nullable()->constrained('assignees')->nullOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('asset_types')->nullOnDelete();
            $table->string('serial_number')->unique();
            $table->string('serial_no')->nullable();
            $table->text('item_description')->nullable();
            $table->string('person_assigned')->nullable();
            $table->foreignId('asset_location_id');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('fund_source_id');
            $table->foreignId('sub_location_id');
            $table->boolean('has_warranty')->default(false);
            $table->boolean('has_documents')->default(false);
            $table->date('asset_warranty_start')->nullable();
            $table->date('asset_warranty_end')->nullable();
            $table->date('warranty_start')->nullable();
            $table->unsignedInteger('warranty_months')->nullable();
            $table->unsignedInteger('maintenance_interval_months')->default(0);
            $table->date('last_maintenance_at')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('region_id');
            $table->string('sub_location')->nullable();
            $table->date('acquisition_date');
            $table->date('purchase_date')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->date('transfer_date')->nullable();
            $table->enum('status', ['active', 'in_transfer_process', 'in_use', 'maintenance', 'retired', 'disposed', 'pending_approval'])->default('active');
            $table->decimal('original_value', 10, 2)->nullable();
            $table->boolean('submitted_for_approval')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null');
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
        Schema::dropIfExists('assets');
        Schema::enableForeignKeyConstraints();
    }
};
