<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Facility;
use App\Models\User;
use App\Models\Warehouse;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Facility::class)->onDelete('cascade');
            $table->foreignIdFor(User::class)->onDelete('cascade');
            $table->foreignIdFor(Warehouse::class)->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->enum('status', ['pending', 'approved', 'rejected', 'in processing', 'dispatched', 'delivered'])->default('pending');
            $table->integer('number_items');
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
