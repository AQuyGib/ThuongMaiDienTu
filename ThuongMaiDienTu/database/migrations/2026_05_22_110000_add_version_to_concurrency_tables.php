<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        $tables = ['products', 'product_variants', 'suppliers', 'inventory_items'];
        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'version')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->unsignedInteger('version')->default(1);
                });
            }
        }
    }

    public function down() {
        $tables = ['products', 'product_variants', 'suppliers', 'inventory_items'];
        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'version')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('version');
                });
            }
        }
    }
};
