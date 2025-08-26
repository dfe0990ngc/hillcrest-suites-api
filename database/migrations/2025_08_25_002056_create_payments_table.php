<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', [
                'cash',
                'credit_card', 
                'debit_card',
                'bank_transfer',
                'mobile_payment',
                'check'
            ]);
            $table->string('payment_reference')->nullable();
            $table->date('payment_date');
            $table->enum('status', ['completed', 'pending', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('receipt_url')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['status', 'payment_date']);
            $table->index(['booking_id', 'status']);
            $table->index(['user_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};