<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('category_id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('name', 50);
            $table->foreign('parent_id')->references('category_id')->on('categories')->onDelete('set null');
        });
    }
    public function down() { Schema::dropIfExists('categories'); }
};