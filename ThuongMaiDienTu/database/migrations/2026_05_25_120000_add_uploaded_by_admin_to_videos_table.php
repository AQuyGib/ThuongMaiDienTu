<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->boolean('uploaded_by_admin')->default(false)->after('user_id');
            $table->index(['uploaded_by_admin', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex(['uploaded_by_admin', 'status']);
            $table->dropColumn('uploaded_by_admin');
        });
    }
};
