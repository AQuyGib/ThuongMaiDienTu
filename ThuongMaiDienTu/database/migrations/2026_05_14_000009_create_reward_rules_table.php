<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reward_rules', function (Blueprint $table) {
            $table->increments('rule_id');
            $table->string('rule_key', 100)->unique();
            $table->string('rule_name', 150);
            $table->text('rule_value')->nullable();
            $table->enum('value_type', ['integer', 'decimal', 'boolean', 'json', 'string'])->default('string');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_rules');
    }
};
