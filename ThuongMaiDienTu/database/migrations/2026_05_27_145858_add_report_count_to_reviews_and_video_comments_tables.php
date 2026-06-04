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
            $table->integer('report_count')->default(0)->after('is_approved');
        });

        Schema::table('video_comments', function (Blueprint $table) {
            $table->integer('report_count')->default(0)->after('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('report_count');
        });

        Schema::table('video_comments', function (Blueprint $table) {
            $table->dropColumn('report_count');
        });
    }
};
