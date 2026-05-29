<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('Pending', 'Shipping', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending'");
            return;
        }

        if ($driver === 'sqlite') {
            // Drop index truoc de tranh loi khoa phu thuoc trong SQLite
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_user_id_status_index');
            });

            Schema::table('orders', function (Blueprint $table) {
                $table->string('status_tmp', 20)->default('Pending');
            });

            DB::table('orders')->update(['status_tmp' => DB::raw('status')]);

            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('orders', function (Blueprint $table) {
                $table->enum('status', ['Pending', 'Shipping', 'Delivered', 'Cancelled'])->default('Pending');
            });

            DB::table('orders')->update(['status' => DB::raw('status_tmp')]);

            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('status_tmp');
            });

            // Tai tao lai index ghep sau khi doi cau truc thanh cong
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'orders_user_id_status_index');
            });
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('Pending', 'Shipping', 'Delivered') NOT NULL DEFAULT 'Pending'");
            return;
        }

        if ($driver === 'sqlite') {
            // Drop index truoc de tranh loi khoa phu thuoc trong SQLite
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex('orders_user_id_status_index');
            });

            Schema::table('orders', function (Blueprint $table) {
                $table->string('status_tmp', 20)->default('Pending');
            });

            DB::table('orders')->update(['status_tmp' => DB::raw('status')]);

            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('orders', function (Blueprint $table) {
                $table->enum('status', ['Pending', 'Shipping', 'Delivered'])->default('Pending');
            });

            DB::table('orders')->update(['status' => DB::raw('status_tmp')]);

            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('status_tmp');
            });

            // Tai tao lai index ghep sau khi doi cau truc thanh cong
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'orders_user_id_status_index');
            });
        }
    }
};
