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
        Schema::table('videos', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->after('video_path');
            $table->string('category', 100)->nullable()->after('youtube_url');
            $table->string('duration', 50)->nullable()->after('category');
            $table->unsignedInteger('views')->default(0)->after('duration');
            $table->unsignedInteger('likes')->default(0)->after('views');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['youtube_url', 'category', 'duration', 'views', 'likes']);
        });
    }
};
