<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reward_catalog', function (Blueprint $table) {
            $table->increments('reward_id');
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->enum('reward_type', ['voucher', 'shipping', 'product', 'wheel_prize']);
            $table->enum('reward_category', ['free_ship', 'discount', 'gift', 'wheel'])->default('discount');
            $table->unsignedBigInteger('points_cost');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('shipping_discount_amount')->default(0);
            $table->unsignedInteger('stock')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['reward_type', 'reward_category']);
            $table->index(['is_active', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_catalog');
    }
};
