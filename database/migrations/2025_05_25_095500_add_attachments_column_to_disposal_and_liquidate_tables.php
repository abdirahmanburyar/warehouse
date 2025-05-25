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
        // Add attachments column to disposals table
        Schema::table('disposals', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('note');
        });

        // Add attachments column to liquidates table
        Schema::table('liquidates', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove attachments column from disposals table
        Schema::table('disposals', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });

        // Remove attachments column from liquidates table
        Schema::table('liquidates', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};
