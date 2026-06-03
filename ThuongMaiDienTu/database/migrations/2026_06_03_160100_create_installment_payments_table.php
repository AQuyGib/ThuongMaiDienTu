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
        Schema::create('installment_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('installment_id');
            $table->unsignedTinyInteger('term_number');
            $table->unsignedBigInteger('amount');
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            
            $table->enum('status', ['Unpaid', 'Paid', 'Overdue'])->default('Unpaid');
            $table->string('transaction_code', 100)->nullable();
            $table->timestamps();
            
            $table->foreign('installment_id')->references('id')->on('installments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_payments');
    }
};
