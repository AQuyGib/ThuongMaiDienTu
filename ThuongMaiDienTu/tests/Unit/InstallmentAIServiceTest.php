<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\InstallmentAIService;
use App\Models\Installment;

class TestableInstallmentAIService extends InstallmentAIService
{
    public $savedResult = null;
    
    protected function saveAIResult(Installment $installment, array $result): void
    {
        $this->savedResult = $result;
    }
}

class InstallmentAIServiceTest extends TestCase
{
    protected $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiService = new TestableInstallmentAIService();
    }

    /**
     * Test low risk assessment logic.
     */
    public function test_low_risk_assessment()
    {
        $installment = new Installment([
            'customer_name' => 'Nguyen Van A',
            'customer_phone' => '0987654321',
            'customer_id_card' => '123456789012',
            'loan_amount' => 10000000,
            'prepay_amount' => 3000000,
            'monthly_payment' => 1200000,
            'period' => 6,
            'trade_in' => false,
            'partner' => 'Shinhan Finance',
            'method' => 'financial_company'
        ]);

        // Force fallback behavior
        putenv('GEMINI_API_KEY=');

        $result = $this->aiService->analyzeInstallment($installment);

        $this->assertEquals('Low', $result['risk_level']);
        $this->assertEquals('Approve', $result['recommendation']);
        $this->assertLessThan(35, $result['risk_score']);
    }

    /**
     * Test medium risk assessment logic.
     */
    public function test_medium_risk_assessment()
    {
        $installment = new Installment([
            'customer_name' => 'Bao',
            'customer_phone' => '0987654321',
            'customer_id_card' => '123456789012',
            'loan_amount' => 25000000, // high loan amount triggers medium risk
            'prepay_amount' => 7500000,
            'monthly_payment' => 3000000,
            'period' => 6,
            'trade_in' => false,
            'partner' => 'Shinhan Finance',
            'method' => 'financial_company'
        ]);

        putenv('GEMINI_API_KEY=');

        $result = $this->aiService->analyzeInstallment($installment);

        $this->assertEquals('Medium', $result['risk_level']);
        $this->assertEquals('Review', $result['recommendation']);
    }

    /**
     * Test high risk assessment logic.
     */
    public function test_high_risk_assessment()
    {
        $installment = new Installment([
            'customer_name' => 'test user', // bad name
            'customer_phone' => '0000000000', // bad phone
            'customer_id_card' => '123', // bad cccd
            'loan_amount' => 10000000,
            'prepay_amount' => 3000000,
            'monthly_payment' => 1200000,
            'period' => 6,
            'trade_in' => false,
            'partner' => 'Shinhan Finance',
            'method' => 'financial_company'
        ]);

        putenv('GEMINI_API_KEY=');

        $result = $this->aiService->analyzeInstallment($installment);

        $this->assertEquals('High', $result['risk_level']);
        $this->assertEquals('Reject', $result['recommendation']);
        $this->assertGreaterThan(75, $result['risk_score']);
    }
}
