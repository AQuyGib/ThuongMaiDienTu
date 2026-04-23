<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->increments('session_id');
            $table->unsignedInteger('user_id');
            $table->string('token', 255);
            $table->string('device_info', 200)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('last_active')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }
    public function down() { Schema::dropIfExists('user_sessions'); }
};