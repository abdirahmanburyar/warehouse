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
        Schema::create('asset_items', function (Blueprint $table) {
            $table->id();
            $table->string('asset_number')->unique(); // Variable length for sequential numbers (0001, 9999, 10000, etc.)
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            
            // AssetItem specific fields
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('serial_number')->unique(); // Moved here - belongs to asset_items
            $table->string('model_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit_of_measure')->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->string('condition')->default('good'); // good, fair, poor, damaged
            $table->string('location_details')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            // Fields from Asset model (excluding acquisition_date, fund_source_id, and serial_number)
            $table->uuid('uuid')->nullable();
            $table->string('tag_no')->nullable();
            $table->string('asset_tag');
            $table->foreignId('asset_category_id');
            $table->foreignId('type_id')->nullable()->constrained('asset_types')->nullOnDelete();
            $table->string('serial_no')->nullable();
            $table->text('item_description')->nullable();
            $table->string('person_assigned')->nullable();
            $table->foreignId('asset_location_id');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('region_id');
            $table->foreignId('sub_location_id');
            $table->boolean('has_warranty')->default(false);
            $table->boolean('has_documents')->default(false);
            $table->date('asset_warranty_start')->nullable();
            $table->date('asset_warranty_end')->nullable();
            $table->date('warranty_start')->nullable();
            $table->unsignedInteger('warranty_months')->nullable();
            $table->unsignedInteger('maintenance_interval_months')->default(0);
            $table->date('last_maintenance_at')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->date('transfer_date')->nullable();
            $table->enum('status', ['active', 'in_transfer_process', 'in_use', 'maintenance', 'retired', 'disposed', 'pending_approval'])->default('active');
            $table->decimal('original_value', 10, 2)->nullable();
            $table->boolean('submitted_for_approval')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('sub_location')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_items');
    }
};
