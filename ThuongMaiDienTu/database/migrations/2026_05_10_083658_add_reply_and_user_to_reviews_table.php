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
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable();
            $table->string('author_name')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            
            // Mối quan hệ nếu cần thiết (không bắt buộc nhưng tốt cho toàn vẹn dữ liệu)
            // $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            // $table->foreign('parent_id')->references('id')->on('reviews')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'author_name', 'parent_id']);
        });
    }
};
