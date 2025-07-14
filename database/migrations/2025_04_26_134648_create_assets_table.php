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
            $table->string('asset_tag');
            $table->foreignId('asset_category_id');
            $table->string('serial_number')->unique();
            $table->text('item_description');
            $table->string('person_assigned')->nullable();
            $table->foreignId('asset_location_id');
            $table->foreignId('fund_source_id');
            $table->foreignId('sub_location_id');
            $table->boolean('has_warranty')->default(false);
            $table->boolean('has_documents')->default(false);
            $table->date('asset_warranty_start');
            $table->date('asset_warranty_end');
            $table->foreignId('region_id');
            $table->string('sub_location')->nullable();
            $table->date('acquisition_date');
            $table->date('transfer_date')->nullable();
            $table->enum('status', ['active', 'in_use', 'maintenance', 'retired', 'disposed', 'pending_approval'])->default('active');
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
        Schema::dropIfExists('assets');
    }
};
