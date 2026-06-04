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
        Schema::create('installments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->string('installment_code', 50)->unique();
            
            // Phân loại hình thức trả góp
            $table->enum('method', ['financial_company', 'credit_card', 'kredivo']);
            $table->string('partner', 100);
            $table->string('card_type', 50)->nullable();
            $table->unsignedInteger('period');
            
            // Tính toán số tiền
            $table->unsignedBigInteger('product_price');
            $table->unsignedBigInteger('prepay_amount');
            $table->unsignedBigInteger('loan_amount');
            $table->unsignedBigInteger('monthly_payment');
            $table->decimal('interest_rate', 5, 4)->default(0);
            $table->unsignedBigInteger('service_fee')->default(0);
            $table->unsignedBigInteger('total_payment');
            $table->unsignedBigInteger('difference_amount');
            
            // Thông tin khách hàng
            $table->string('customer_name', 255);
            $table->string('customer_phone', 50);
            $table->string('customer_id_card', 50)->nullable();
            $table->boolean('trade_in')->default(false);
            
            // Trạng thái phê duyệt
            $table->enum('status', [
                'Pending_Approval',
                'Approved',
                'Rejected',
                'Paying',
                'Completed',
                'Cancelled'
            ])->default('Pending_Approval');
            
            $table->string('rejection_reason', 255)->nullable();
            
            // AI Fields
            $table->unsignedTinyInteger('ai_risk_score')->nullable();
            $table->string('ai_risk_level', 20)->nullable();
            $table->json('ai_analysis')->nullable();
            
            $table->timestamps();
            
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
