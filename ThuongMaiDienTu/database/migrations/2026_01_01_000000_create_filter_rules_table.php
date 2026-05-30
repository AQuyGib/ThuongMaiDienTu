<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('filter_rules', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->comment('Group key like brand, ram, cpu');
            $table->string('key', 100)->comment('Specific rule key');
            $table->string('display_name', 255)->nullable();
            $table->string('type', 20)->default('choice')->comment('choice, range, bool');
            $table->json('conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filter_rules');
    }
};
