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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // General
            $table->string('hotel_name',255)->nullable();
            $table->enum('currency',['PHP','USD','AUD','JPY'])->nullable()->default('PHP');
            $table->text('hotel_address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->time('check_in')->nullable()->default('15:00');
            $table->time('check_out')->nullable()->default('11:00');
            $table->decimal('tax_rate')->nullable()->default(10);

            // Notifications
            $table->boolean('notify_new_booking')->nullable()->default(true);
            $table->boolean('notify_booking_cancellation')->nullable()->default(true);
            $table->boolean('notify_booking_payment_confirmation')->nullable()->default(true);
            $table->boolean('enable_push_notification')->nullable()->default(false);

            // Security
            $table->integer('session_timeout')->nullable()->default(60);
            $table->enum('password_policy',['basic','strong','very_strong'])->nullable()->default('basic');

            // Integrations
            $table->json('smtp')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
