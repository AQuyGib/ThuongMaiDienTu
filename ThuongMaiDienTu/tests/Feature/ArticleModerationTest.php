<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Role;
use App\Models\User;
use App\Models\RewardPoint;
use App\Services\ArticleAIService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->seed(RoleSeeder::class);

        // Create a customer user
        $this->customer = User::create([
            'role_id' => 3, // Customer
            'full_name' => 'John Customer',
            'email' => 'customer@example.com',
            'password_hash' => bcrypt('password'),
        ]);

        // Create an admin user
        $this->admin = User::create([
            'role_id' => 1, // Admin
            'full_name' => 'System Admin',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('password'),
        ]);
    }

    /**
     * Test Auto-Moderation approves high-quality articles during creation
     */
    public function test_article_creation_auto_approved(): void
    {
        $mockAnalysis = [
            'quality_score' => 85,
            'recommended_reward_points' => 30,
            'is_spam' => false,
            'has_sensitive_content' => false,
            'plagiarism_probability' => 10,
            'moderation_verdict' => 'approved',
            'moderation_reason' => 'Nội dung rất tốt và chuẩn SEO.',
            'tags' => ['#tips', '#iphone'],
            'seo' => [
                'title_suggestion' => 'Tiêu đề tối ưu từ AI',
                'meta_description_suggestion' => 'Mô tả meta tối ưu từ AI',
                'keywords_analysis' => [],
                'seo_score' => 80,
                'optimization_tips' => []
            ]
        ];

        // Mock the ArticleAIService
        $this->mock(ArticleAIService::class, function ($mock) use ($mockAnalysis) {
            $mock->shouldReceive('analyzeArticle')->once()->andReturn($mockAnalysis);
        });

        $response = $this->actingAs($this->customer)->post(route('articles.store'), [
            'title' => 'Bài viết công nghệ chất lượng cao',
            'summary' => 'Tóm tắt bài viết công nghệ mới',
            'content' => 'Nội dung chi tiết của bài viết công nghệ rất dài và bổ ích...',
        ]);

        $response->assertRedirect(route('articles.index'));
        $response->assertSessionHas('success');

        // Check article attributes
        $this->assertDatabaseHas('articles', [
            'title' => 'Bài viết công nghệ chất lượng cao',
            'author_id' => $this->customer->user_id,
            'author_type' => 'customer',
            'status' => 'approved',
            'reward_points_awarded' => 30,
            'ai_quality_score' => 85,
            'ai_moderation_verdict' => 'approved',
            'ai_checked' => 1,
        ]);

        // Check customer points history
        $this->assertDatabaseHas('reward_points', [
            'user_id' => $this->customer->user_id,
            'points' => 30,
            'type' => 'earned',
        ]);
    }

    /**
     * Test Auto-Moderation rejects violating articles during creation
     */
    public function test_article_creation_auto_rejected(): void
    {
        $mockAnalysis = [
            'quality_score' => 30,
            'recommended_reward_points' => 0,
            'is_spam' => true,
            'has_sensitive_content' => true,
            'plagiarism_probability' => 80,
            'moderation_verdict' => 'rejected',
            'moderation_reason' => 'Bài viết chứa nhiều liên kết quảng cáo spam.',
            'tags' => ['#spam'],
            'seo' => [
                'title_suggestion' => 'Tiêu đề tối ưu từ AI',
                'meta_description_suggestion' => 'Mô tả meta tối ưu từ AI',
                'keywords_analysis' => [],
                'seo_score' => 20,
                'optimization_tips' => []
            ]
        ];

        // Mock the ArticleAIService
        $this->mock(ArticleAIService::class, function ($mock) use ($mockAnalysis) {
            $mock->shouldReceive('analyzeArticle')->once()->andReturn($mockAnalysis);
        });

        $response = $this->actingAs($this->customer)->post(route('articles.store'), [
            'title' => 'Bài viết spam mua bán bậy bạ',
            'summary' => 'Tóm tắt bài viết quảng cáo',
            'content' => 'Link mua bán bậy bạ link mua bán bậy bạ...',
        ]);

        $response->assertRedirect(route('articles.index'));
        $response->assertSessionHas('error');

        // Check article attributes
        $this->assertDatabaseHas('articles', [
            'title' => 'Bài viết spam mua bán bậy bạ',
            'author_id' => $this->customer->user_id,
            'author_type' => 'customer',
            'status' => 'rejected',
            'reward_points_awarded' => 0,
            'ai_quality_score' => 30,
            'ai_moderation_verdict' => 'rejected',
            'ai_checked' => 1,
        ]);

        // Verify no reward points awarded
        $this->assertEquals(0, RewardPoint::where('user_id', $this->customer->user_id)->count());
    }

    /**
     * Test Admin approving a pending article and rewarding points
     */
    public function test_admin_approve_article_success(): void
    {
        $article = Article::create([
            'title' => 'Bài viết chờ duyệt',
            'slug' => 'bai-viet-cho-duyet',
            'content' => 'Nội dung bài viết chờ duyệt...',
            'author_id' => $this->customer->user_id,
            'author_type' => 'customer',
            'status' => 'pending',
            'reward_points_awarded' => 0,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.articles.approve', $article->article_id), [
            'points' => 50,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $article->refresh();
        $this->assertEquals('approved', $article->status);
        $this->assertEquals(50, $article->reward_points_awarded);

        // Check customer points history
        $this->assertDatabaseHas('reward_points', [
            'user_id' => $this->customer->user_id,
            'points' => 50,
            'type' => 'earned',
        ]);
    }

    /**
     * Test Admin rejecting a pending article
     */
    public function test_admin_reject_article_success(): void
    {
        $article = Article::create([
            'title' => 'Bài viết chờ duyệt 2',
            'slug' => 'bai-viet-cho-duyet-2',
            'content' => 'Nội dung bài viết chờ duyệt...',
            'author_id' => $this->customer->user_id,
            'author_type' => 'customer',
            'status' => 'pending',
            'reward_points_awarded' => 0,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.articles.reject', $article->article_id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $article->refresh();
        $this->assertEquals('rejected', $article->status);
        $this->assertEquals(0, $article->reward_points_awarded);

        // Verify no reward points awarded
        $this->assertEquals(0, RewardPoint::where('user_id', $this->customer->user_id)->count());
    }

    /**
     * Test Customer editing an article and logic resets status and handles points re-award prevention
     */
    public function test_customer_edit_prevents_double_reward(): void
    {
        // Initially approved and rewarded
        $article = Article::create([
            'title' => 'Bài viết cũ',
            'slug' => 'bai-viet-cu',
            'content' => 'Nội dung bài viết cũ...',
            'author_id' => $this->customer->user_id,
            'author_type' => 'customer',
            'status' => 'approved',
            'reward_points_awarded' => 30,
        ]);

        // Record the initial points transaction
        RewardPoint::create([
            'user_id' => $this->customer->user_id,
            'points' => 30,
            'type' => 'earned',
            'reason' => 'Thưởng điểm đóng góp bài viết: Bài viết cũ',
        ]);

        $mockAnalysis = [
            'quality_score' => 88,
            'recommended_reward_points' => 40,
            'is_spam' => false,
            'has_sensitive_content' => false,
            'plagiarism_probability' => 5,
            'moderation_verdict' => 'approved',
            'moderation_reason' => 'Bài viết cập nhật tốt.',
            'tags' => ['#tips', '#iphone'],
            'seo' => [
                'title_suggestion' => 'Tiêu đề tối ưu từ AI',
                'meta_description_suggestion' => 'Mô tả meta tối ưu từ AI',
                'keywords_analysis' => [],
                'seo_score' => 90,
                'optimization_tips' => []
            ]
        ];

        // Mock the ArticleAIService
        $this->mock(ArticleAIService::class, function ($mock) use ($mockAnalysis) {
            $mock->shouldReceive('analyzeArticle')->once()->andReturn($mockAnalysis);
        });

        // Update the article
        $response = $this->actingAs($this->customer)->put(route('articles.update', $article->article_id), [
            'title' => 'Bài viết cũ đã cập nhật',
            'summary' => 'Tóm tắt bài viết cũ cập nhật',
            'content' => 'Nội dung bài viết cũ cập nhật mới...',
        ]);

        $response->assertRedirect(route('articles.index'));
        $response->assertSessionHas('success');

        $article->refresh();
        $this->assertEquals('approved', $article->status);
        // The points awarded must still be 30, not updated to 40 since it had points before!
        $this->assertEquals(30, $article->reward_points_awarded);

        // Customer should still only have 1 points log of 30 points (total points transaction count = 1)
        $this->assertEquals(1, RewardPoint::where('user_id', $this->customer->user_id)->count());
    }

    /**
     * Test Admin bulk approving AI-qualified pending articles
     */
    public function test_admin_bulk_approve_ai_articles(): void
    {
        // 1. Article qualified by AI and pending
        $qualifiedArticle = Article::create([
            'title' => 'Bài viết đạt chuẩn 1',
            'slug' => 'bai-viet-dat-chuan-1',
            'content' => 'Nội dung đạt chuẩn...',
            'author_id' => $this->customer->user_id,
            'author_type' => 'customer',
            'status' => 'pending',
            'ai_checked' => 1,
            'ai_moderation_verdict' => 'approved',
            'ai_analysis' => ['recommended_reward_points' => 25],
        ]);

        // 2. Article flagged by AI and pending (should NOT be approved by bulk action)
        $flaggedArticle = Article::create([
            'title' => 'Bài viết nghi ngờ 2',
            'slug' => 'bai-viet-nghi-ngo-2',
            'content' => 'Nội dung nghi ngờ...',
            'author_id' => $this->customer->user_id,
            'author_type' => 'customer',
            'status' => 'pending',
            'ai_checked' => 1,
            'ai_moderation_verdict' => 'flagged',
            'ai_analysis' => ['recommended_reward_points' => 10],
        ]);

        // 3. Article not checked by AI (should NOT be approved by bulk action)
        $unqualifiedArticle = Article::create([
            'title' => 'Bài viết chưa quét 3',
            'slug' => 'bai-viet-chua-quet-3',
            'content' => 'Nội dung chưa quét...',
            'author_id' => $this->customer->user_id,
            'author_type' => 'customer',
            'status' => 'pending',
            'ai_checked' => 0,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.articles.bulk-approve-ai'));

        $response->assertRedirect(route('admin.articles.index'));
        $response->assertSessionHas('success');

        $qualifiedArticle->refresh();
        $flaggedArticle->refresh();
        $unqualifiedArticle->refresh();

        // Check status
        $this->assertEquals('approved', $qualifiedArticle->status);
        $this->assertEquals(25, $qualifiedArticle->reward_points_awarded);

        $this->assertEquals('pending', $flaggedArticle->status);
        $this->assertEquals('pending', $unqualifiedArticle->status);

        // Check points history (only 1 earned entry of 25 points)
        $this->assertDatabaseHas('reward_points', [
            'user_id' => $this->customer->user_id,
            'points' => 25,
            'type' => 'earned',
        ]);
        $this->assertEquals(1, RewardPoint::where('user_id', $this->customer->user_id)->count());
    }
}
