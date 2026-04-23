<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('setting_key', 50)->primary();
            $table->text('setting_value')->nullable();
        });
    }
    public function down() { Schema::dropIfExists('settings'); }
};