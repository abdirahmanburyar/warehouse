<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_phone')->nullable();
            $table->string('manager_email')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('capacity')->nullable()->comment('Storage capacity in cubic meters');
            $table->integer('temperature_min')->nullable()->comment('Minimum temperature in Celsius');
            $table->integer('temperature_max')->nullable()->comment('Maximum temperature in Celsius');
            $table->decimal('humidity_min', 5, 2)->nullable()->comment('Minimum humidity percentage');
            $table->decimal('humidity_max', 5, 2)->nullable()->comment('Maximum humidity percentage');
            $table->string('status', 50)->default('active');
            $table->foreignIdFor(User::class)->cascadeOnDelete();
            $table->boolean('has_cold_storage')->default(false);
            $table->boolean('has_hazardous_storage')->default(false);
            $table->text('special_handling_capabilities')->nullable();
            $table->boolean('is_24_hour')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
