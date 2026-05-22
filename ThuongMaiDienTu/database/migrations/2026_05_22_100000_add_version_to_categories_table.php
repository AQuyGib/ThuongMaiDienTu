<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        if (!Schema::hasColumn('categories', 'version')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedInteger('version')->default(1)->after('name');
            });
        }
    }

    public function down() {
        if (Schema::hasColumn('categories', 'version')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('version');
            });
        }
    }
};
