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
        Schema::table('warranty_claims', function (Blueprint $table) {
            $table->unsignedBigInteger('refund_amount')->nullable()->after('admin_note')->comment('Số tiền hoàn trả cho khách');
            $table->enum('refund_method', ['cash', 'bank_transfer'])->nullable()->after('refund_amount')->comment('Phương thức hoàn tiền');
            $table->timestamp('refunded_at')->nullable()->after('refund_method')->comment('Thời điểm hoàn tiền thực tế');
            $table->string('bank_name')->nullable()->after('refunded_at')->comment('Tên ngân hàng của khách');
            $table->string('bank_account_number')->nullable()->after('bank_name')->comment('Số tài khoản ngân hàng');
            $table->string('bank_account_name')->nullable()->after('bank_account_number')->comment('Tên chủ tài khoản ngân hàng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            $table->dropColumn([
                'refund_amount', 
                'refund_method', 
                'refunded_at',
                'bank_name',
                'bank_account_number',
                'bank_account_name'
            ]);
        });
    }
};
