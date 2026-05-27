<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->string('locale', 10);
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->unique(['page_id', 'locale']);
            $table->index('locale');
            $table->foreign('page_id')
                ->references('page_id')
                ->on('pages')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_translations');
    }
};
