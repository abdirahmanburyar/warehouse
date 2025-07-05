<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if dosages table exists, if not create it first
        if (!Schema::hasTable('dosages')) {
            Schema::create('dosages', function (Blueprint $table) {
                $table->id();
                $table->integer('productID')->unique();
                $table->string('name')->unique();
                $table->foreignIdFor(Category::class)->cascadeOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('productID')->unique();
            $table->string('name');
            $table->foreignIdFor(Category::class)->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('dosage_id')->nullable();
            // $table->string('movement');
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('products');
        Schema::dropIfExists('dosages');
        Schema::enableForeignKeyConstraints();
    }
};
