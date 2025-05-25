<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Drop the existing table
        Schema::dropIfExists('warehouses');
        
        // Create the new table with updated structure
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable()->comment('Street name');
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_phone')->nullable();
            $table->string('manager_email')->nullable();
            $table->string('status')->default('active');
            $table->foreignIdFor(User::class)->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Add foreign key constraints
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
        });
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        Schema::dropIfExists('warehouses');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
