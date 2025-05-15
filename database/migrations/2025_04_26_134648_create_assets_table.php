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
            $table->foreignId('sub_location_id');
            $table->string('sub_location')->nullable();
            $table->date('acquisition_date');
            $table->enum('status', ['active', 'in_use', 'maintenance', 'retired', 'disposed'])->default('active');
            $table->decimal('original_value', 10, 2)->nullable();
            $table->string('source_agency')->nullable();
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
