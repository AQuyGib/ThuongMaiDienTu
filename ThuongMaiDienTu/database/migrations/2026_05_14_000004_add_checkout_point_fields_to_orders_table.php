<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'order_code')) {
                $table->string('order_code', 50)->nullable()->after('order_id');
            }
            if (! Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name', 150)->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('orders', 'customer_phone')) {
                $table->string('customer_phone', 30)->nullable()->after('customer_name');
            }
            if (! Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('customer_phone');
            }
            if (! Schema::hasColumn('orders', 'note')) {
                $table->text('note')->nullable()->after('shipping_address');
            }
            if (! Schema::hasColumn('orders', 'discount_amount')) {
                $table->unsignedBigInteger('discount_amount')->default(0)->after('shipping_fee');
            }
            if (! Schema::hasColumn('orders', 'wallet_points_used')) {
                $table->unsignedBigInteger('wallet_points_used')->default(0)->after('discount_amount');
            }
            if (! Schema::hasColumn('orders', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->after('payment_method');
            }
            if (! Schema::hasColumn('orders', 'points_status')) {
                $table->enum('points_status', ['pending', 'processed', 'refunded', 'cancelled'])->default('pending')->after('payment_status');
            }
            if (! Schema::hasColumn('orders', 'points_processed_at')) {
                $table->timestamp('points_processed_at')->nullable()->after('points_status');
            }

            $table->index(['user_id', 'status']);
            $table->index(['order_code']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['order_code', 'customer_name', 'customer_phone', 'shipping_address', 'note', 'discount_amount', 'wallet_points_used', 'payment_status', 'points_status', 'points_processed_at'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
