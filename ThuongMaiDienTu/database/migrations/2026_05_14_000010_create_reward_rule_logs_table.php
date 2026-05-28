<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reward_rule_logs', function (Blueprint $table) {
            $table->bigIncrements('log_id');
            $table->unsignedInteger('rule_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('action', 50);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('rule_id')->references('rule_id')->on('reward_rules')->nullOnDelete();
            $table->foreign('user_id')->references('user_id')->on('users')->nullOnDelete();
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_rule_logs');
    }
};
