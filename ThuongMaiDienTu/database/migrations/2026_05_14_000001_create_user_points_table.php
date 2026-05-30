<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_points', function (Blueprint $table) {
            $table->increments('user_points_id');
            $table->unsignedInteger('user_id')->unique();
            $table->unsignedBigInteger('wallet_points')->default(0);
            $table->unsignedBigInteger('rank_points')->default(0);
            $table->unsignedBigInteger('wallet_total_earned')->default(0);
            $table->unsignedBigInteger('wallet_total_used')->default(0);
            $table->unsignedBigInteger('rank_total_earned')->default(0);
            $table->string('current_rank', 50)->default('Bronze');
            $table->timestamp('last_rank_updated_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_points');
    }
};
