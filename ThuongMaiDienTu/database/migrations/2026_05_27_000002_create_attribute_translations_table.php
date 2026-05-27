<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            $table->string('locale', 10);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['attribute_id', 'locale']);
            $table->index('locale');
            $table->foreign('attribute_id')
                ->references('attribute_id')
                ->on('attributes')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_translations');
    }
};
