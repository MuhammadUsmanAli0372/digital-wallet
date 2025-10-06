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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('transactions_sender_id_foreign')
                ->index();

            $table->foreignId('receiver_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->name('transactions_receiver_id_foreign')
                ->index();

            $table->decimal('amount', 16, 2);
            $table->decimal('commission_fee', 16, 2)->default(0);
            $table->decimal('total_debited', 16, 2)->storedAs('amount + commission_fee'); // computed column
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
