<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('locale', 10);
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->unique(['category_id', 'locale']);
            $table->index('locale');

            $table->foreign('category_id')
                ->references('category_id')
                ->on('categories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_translations');
    }
};
