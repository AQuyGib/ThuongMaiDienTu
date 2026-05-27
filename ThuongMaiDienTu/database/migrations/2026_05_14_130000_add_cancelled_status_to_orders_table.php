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
        }
    }
};
