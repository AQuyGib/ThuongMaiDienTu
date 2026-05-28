<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filter_rules', function (Blueprint $table) {
            if (Schema::hasColumn('filter_rules', 'group') && !Schema::hasColumn('filter_rules', 'group_key')) {
                $table->string('group_key', 50)->nullable()->after('id');
            }
            if (Schema::hasColumn('filter_rules', 'key') && !Schema::hasColumn('filter_rules', 'rule_key')) {
                $table->string('rule_key', 100)->nullable()->after('group_key');
            }
        });

        if (Schema::hasTable('filter_rules')) {
            DB::table('filter_rules')->update([
                'group_key' => DB::raw('`group`'),
                'rule_key' => DB::raw('`key`'),
            ]);
        }

        Schema::table('filter_rules', function (Blueprint $table) {
            if (Schema::hasColumn('filter_rules', 'group')) {
                $table->dropUnique(['group', 'key']);
            }
            if (Schema::hasColumn('filter_rules', 'group')) {
                $table->dropColumn('group');
            }
            if (Schema::hasColumn('filter_rules', 'key')) {
                $table->dropColumn('key');
            }
            $table->unique(['group_key', 'rule_key']);
            $table->index(['group_key', 'is_active', 'sort_order'], 'filter_rules_group_active_sort_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->json('filter_config_v2')->nullable()->after('filter_config');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('base_price_generated')->nullable()->storedAs('`base_price`')->comment('Generated price mirror for hot filtering');
            
            $ramGbExpression = DB::getDriverName() === 'sqlite' 
                ? "json_extract(specifications, '$.ram_gb')" 
                : "CAST(JSON_UNQUOTE(JSON_EXTRACT(`specifications`, '$.ram_gb')) AS UNSIGNED)";
                
            $table->unsignedInteger('ram_gb_generated')->nullable()->storedAs($ramGbExpression);
            $table->index('base_price_generated', 'products_base_price_generated_idx');
            $table->index('ram_gb_generated', 'products_ram_gb_generated_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'ram_gb_generated')) {
                $table->dropIndex('products_ram_gb_generated_idx');
                $table->dropColumn('ram_gb_generated');
            }
            if (Schema::hasColumn('products', 'base_price_generated')) {
                $table->dropIndex('products_base_price_generated_idx');
                $table->dropColumn('base_price_generated');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'filter_config_v2')) {
                $table->dropColumn('filter_config_v2');
            }
        });

        Schema::table('filter_rules', function (Blueprint $table) {
            if (Schema::hasColumn('filter_rules', 'group_key')) {
                $table->dropUnique(['group_key', 'rule_key']);
                $table->dropIndex('filter_rules_group_active_sort_idx');
                $table->dropColumn(['group_key', 'rule_key']);
            }
        });
    }
};
