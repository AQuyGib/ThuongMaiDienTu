<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id');
            $table->string('locale', 10);
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'locale']);
            $table->index('locale');
            $table->foreign('product_id')
                ->references('product_id')
                ->on('products')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_translations');
    }
};
