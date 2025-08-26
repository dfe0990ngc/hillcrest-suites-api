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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users','id')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms','id')->cascadeOnDelete();
            $table->string('code',12)->nullable();
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->integer('guests')->nullable()->default(0);
            $table->decimal('total_amount')->nullable()->default(0);
            $table->enum('status',['confirmed','checked_in','checked_out','pending','cancelled'])->nullable()->default('pending');
            $table->enum('payment_status',['paid','pending','unpaid','refund'])->nullable()->default('pending');
            $table->string('special_requests')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
