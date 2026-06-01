<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_order_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedInteger('order_id');
            $table->string('ai_status', 50);
            $table->integer('risk_score');
            $table->text('analysis')->nullable();
            $table->string('trigger_type', 50)->default('auto'); // auto / manual
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_order_logs');
    }
};
