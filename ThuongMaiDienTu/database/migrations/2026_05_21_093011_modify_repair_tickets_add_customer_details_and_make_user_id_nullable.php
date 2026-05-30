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
        Schema::table('repair_tickets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('repair_tickets', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->change();
            $table->string('customer_address')->nullable()->after('customer_phone');
            $table->string('customer_email')->nullable()->after('customer_address');
            $table->string('customer_source')->nullable()->after('customer_email');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_tickets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('repair_tickets', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable(false)->change();
            $table->dropColumn(['customer_address', 'customer_email', 'customer_source']);
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict');
        });
    }
};
