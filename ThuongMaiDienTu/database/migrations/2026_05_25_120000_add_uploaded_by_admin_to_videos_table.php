<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            if (!Schema::hasColumn('videos', 'uploaded_by_admin')) {
                $table->boolean('uploaded_by_admin')->default(false)->after('user_id');
            }
            if (!Schema::hasIndex('videos', 'videos_uploaded_by_admin_status_index')) {
                $table->index(['uploaded_by_admin', 'status']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            if (Schema::hasIndex('videos', 'videos_uploaded_by_admin_status_index')) {
                $table->dropIndex(['uploaded_by_admin', 'status']);
            }
            if (Schema::hasColumn('videos', 'uploaded_by_admin')) {
                $table->dropColumn('uploaded_by_admin');
            }
        });
    }
};
