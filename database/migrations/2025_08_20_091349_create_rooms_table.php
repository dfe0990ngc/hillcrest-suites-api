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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('number',12)->unique()->nullable();
            $table->enum('type',['Standard','Deluxe','Suite','Presidential'])->nullable()->default('Standard');
            $table->decimal('price_per_night')->nullable()->default(0);
            $table->integer('capacity')->nullable()->default(0);
            $table->json('amenities')->nullable()->nullable();
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->enum('status',['Available','Occupied','Maintenance'])->nullable()->default('Available');
            $table->integer('floor')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
