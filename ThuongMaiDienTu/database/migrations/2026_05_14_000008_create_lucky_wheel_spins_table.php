<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lucky_wheel_spins', function (Blueprint $table) {
            $table->bigIncrements('spin_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('reward_id')->nullable();
            $table->string('spin_code', 50)->unique();
            $table->enum('status', ['pending', 'won', 'lost', 'cancelled'])->default('won');
            $table->unsignedBigInteger('points_spent')->default(0);
            $table->json('result_snapshot')->nullable();
            $table->timestamp('spun_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('reward_id')->references('reward_id')->on('reward_catalog')->nullOnDelete();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lucky_wheel_spins');
    }
};
