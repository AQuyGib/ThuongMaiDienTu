<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->bigIncrements('redemption_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('reward_id');
            $table->string('redemption_code', 50)->unique();
            $table->enum('status', ['pending', 'approved', 'issued', 'cancelled'])->default('issued');
            $table->unsignedBigInteger('points_spent');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('shipping_discount_amount')->default(0);
            $table->json('reward_snapshot')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('reward_id')->references('reward_id')->on('reward_catalog')->restrictOnDelete();
            $table->index(['user_id', 'status']);
            $table->index(['reward_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
    }
};
