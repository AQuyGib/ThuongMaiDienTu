<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('ai_chatbot_history', function (Blueprint $table) {
            $table->increments('chat_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->text('user_prompt');
            $table->text('ai_response');
            $table->string('session_token', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }
    public function down() { Schema::dropIfExists('ai_chatbot_history'); }
};