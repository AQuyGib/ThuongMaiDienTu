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
            $table->boolean('is_approved')->default(true)->after('content');
        });

        Schema::table('video_comments', function (Blueprint $table) {
            $table->boolean('is_approved')->default(true)->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });

        Schema::table('video_comments', function (Blueprint $table) {
            $table->dropColumn('is_approved');
        });
    }
};
