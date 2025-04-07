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
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->foreignIdFor(User::class)->cascadeOnDelete();
            $table->string('phone');
            $table->string('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('facility_type')->comment('hospital, clinic, pharmacy, etc');
            $table->boolean('has_cold_storage')->default(false);
            $table->text('special_handling_capabilities')->nullable();
            $table->boolean('is_24_hour')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
