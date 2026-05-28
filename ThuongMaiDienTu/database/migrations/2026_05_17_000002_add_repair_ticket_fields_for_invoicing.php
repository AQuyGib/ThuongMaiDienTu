<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_tickets', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('ticket_id');
            }

            if (!Schema::hasColumn('repair_tickets', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_name');
            }

            if (!Schema::hasColumn('repair_tickets', 'service_name')) {
                $table->string('service_name')->nullable()->after('customer_phone');
            }

            if (!Schema::hasColumn('repair_tickets', 'service_fee')) {
                $table->decimal('service_fee', 15, 2)->default(0)->after('service_name');
            }

            if (!Schema::hasColumn('repair_tickets', 'invoice_no')) {
                $table->string('invoice_no')->nullable()->unique()->after('service_fee');
            }

            if (!Schema::hasColumn('repair_tickets', 'invoiced_at')) {
                $table->timestamp('invoiced_at')->nullable()->after('invoice_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('repair_tickets', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_phone', 'service_name', 'service_fee', 'invoice_no', 'invoiced_at']);
        });
    }
};
