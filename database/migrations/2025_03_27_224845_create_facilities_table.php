<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\District;

use Illuminate\Support\Facades\DB;

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
            $table->string('district');
            $table->string('phone');
            $table->string('address');
            $table->string('facility_type');
            $table->boolean('has_cold_storage')->default(false);
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
         // Disable foreign key checks
         DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
         Schema::dropIfExists('facilities');
         
         // Re-enable foreign key checks
         DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
