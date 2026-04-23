<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->unsignedInteger('role_id');
            $table->string('full_name', 50);
            $table->string('email', 100)->unique();
            $table->string('password_hash', 255);
            $table->boolean('is_2fa_enabled')->default(0);
            $table->string('two_factor_secret', 32)->nullable();
            $table->enum('member_tier', ['Dong', 'Bac', 'Vang'])->default('Dong');
            $table->enum('status', ['Active', 'Banned'])->default('Active');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('restrict');
        });
    }
    public function down() { Schema::dropIfExists('users'); }
};