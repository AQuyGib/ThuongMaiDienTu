<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotSecurityTest extends TestCase
{
    use RefreshDatabase;

    private $role;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        session()->flush();

        // Lấy hoặc tạo vai trò Customer mẫu
        $this->role = Role::firstOrCreate(
            ['name' => 'Customer'],
            ['description' => 'Standard Customer']
        );

        // Tạo user mẫu
        $this->user = User::create([
            'role_id' => $this->role->role_id,
            'full_name' => 'Test User',
            'email' => 'testuser@gmail.com',
            'password_hash' => bcrypt('password123'),
            'status' => 'Active',
            'member_tier' => 'Dong',
            'is_2fa_enabled' => 0,
        ]);
    }

    /**
     * Test chatbot từ chối người dùng chưa đăng nhập.
     */
    public function test_chatbot_rejects_unauthenticated_user()
    {
        $response = $this->postJson('/chatbot', [
            'prompt' => 'Hello AI',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'need_login' => true,
            ]);
    }

    /**
     * Test chatbot từ chối người dùng đã bị cấm.
     */
    public function test_chatbot_rejects_banned_user()
    {
        $bannedUser = User::create([
            'role_id' => $this->role->role_id,
            'full_name' => 'Banned Chat User',
            'email' => 'bannedchat@gmail.com',
            'password_hash' => bcrypt('password123'),
            'status' => 'Active',
            'member_tier' => 'Dong',
            'is_2fa_enabled' => 0,
            'chatbot_banned_until' => now()->addDays(5),
        ]);

        $response = $this->actingAs($bannedUser)
            ->postJson('/chatbot', [
                'prompt' => 'Hello AI',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'is_banned' => true,
            ]);
    }

    /**
     * Test chatbot tự động cấm người dùng gửi tin nhắn trùng lặp liên tục (spam).
     */
    public function test_chatbot_automatically_bans_user_for_repetitive_messages()
    {
        // Giả lập gửi tin trùng lặp 4 lần liên tiếp
        for ($i = 0; $i < 4; $i++) {
            $response = $this->actingAs($this->user)
                ->postJson('/chatbot', [
                    'prompt' => 'Spam message content',
                ]);
            
            if ($i < 3) {
                $this->assertNotEquals(403, $response->getStatusCode());
            } else {
                // Lượt thứ 4 sẽ bị phát hiện spam và trả về 403
                $response->assertStatus(403)
                    ->assertJson([
                        'success' => false,
                        'is_banned' => true,
                    ]);
                
                // Kiểm tra database xem user đã bị cập nhật chatbot_banned_until hay chưa
                $this->user->refresh();
                $this->assertNotNull($this->user->chatbot_banned_until);
                $this->assertTrue($this->user->chatbot_banned_until->isAfter(now()));
            }
        }
    }

    /**
     * Test admin có thể mở khóa chatbot cho người dùng bị cấm.
     */
    public function test_admin_can_unban_chatbot_user()
    {
        // Lấy hoặc cập nhật vai trò Admin với ID 1
        $adminRole = Role::updateOrCreate(
            ['role_id' => 1],
            ['name' => 'Admin', 'description' => 'Administrator']
        );

        $adminUser = User::create([
            'role_id' => $adminRole->role_id,
            'full_name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password_hash' => bcrypt('password123'),
            'status' => 'Active',
            'member_tier' => 'Dong',
            'is_2fa_enabled' => 0,
        ]);

        // Người dùng bị cấm chatbot
        $bannedUser = User::create([
            'role_id' => $this->role->role_id,
            'full_name' => 'Banned User',
            'email' => 'banned@gmail.com',
            'password_hash' => bcrypt('password123'),
            'status' => 'Active',
            'member_tier' => 'Dong',
            'is_2fa_enabled' => 0,
            'chatbot_banned_until' => now()->addDays(30),
        ]);

        // Thực hiện request mở khóa chatbot từ tài khoản admin
        $response = $this->actingAs($adminUser)
            ->postJson("/admin/permissions/{$bannedUser->user_id}/unban-chatbot");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Kiểm tra database xem cột chatbot_banned_until đã được set thành null chưa
        $bannedUser->refresh();
        $this->assertNull($bannedUser->chatbot_banned_until);
    }

    /**
     * Test chức năng Chẩn đoán lỗi AI từ chối người dùng đã bị cấm.
     */
    public function test_ai_diagnose_rejects_banned_user()
    {
        $bannedUser = User::create([
            'role_id' => $this->role->role_id,
            'full_name' => 'Banned User',
            'email' => 'banned@gmail.com',
            'password_hash' => bcrypt('password123'),
            'status' => 'Active',
            'member_tier' => 'Dong',
            'is_2fa_enabled' => 0,
            'chatbot_banned_until' => now()->addDays(5),
        ]);

        $response = $this->actingAs($bannedUser)
            ->postJson('/profile/repair-tickets/ai-diagnose', [
                'issue_desc' => 'Tivi nhà tôi tự nhiên không lên hình mặc dù vẫn có tiếng rè rè',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'is_banned' => true,
            ]);
    }

    /**
     * Test chức năng Chẩn đoán lỗi AI phát hiện hành vi spam và tự động cấm.
     */
    public function test_ai_diagnose_spam_detection()
    {
        // Gửi trùng lặp 4 lần
        for ($i = 0; $i < 4; $i++) {
            $response = $this->actingAs($this->user)
                ->postJson('/profile/repair-tickets/ai-diagnose', [
                    'issue_desc' => 'Thiết bị lỗi hỏng màn hình nghiêm trọng cần sửa chữa gấp',
                ]);
            
            if ($i < 3) {
                $this->assertNotEquals(403, $response->getStatusCode());
            } else {
                $response->assertStatus(403)
                    ->assertJson([
                        'success' => false,
                        'is_banned' => true,
                    ]);
                
                $this->user->refresh();
                $this->assertNotNull($this->user->chatbot_banned_until);
            }
        }
    }

    /**
     * Test chức năng Trợ lý SEO AI bài viết từ chối người dùng đã bị cấm.
     */
    public function test_ai_assist_rejects_banned_user()
    {
        $bannedUser = User::create([
            'role_id' => $this->role->role_id,
            'full_name' => 'Banned User',
            'email' => 'banned@gmail.com',
            'password_hash' => bcrypt('password123'),
            'status' => 'Active',
            'member_tier' => 'Dong',
            'is_2fa_enabled' => 0,
            'chatbot_banned_until' => now()->addDays(5),
        ]);

        $response = $this->actingAs($bannedUser)
            ->postJson('/lifestyle/ai-assist', [
                'title' => 'Bài viết mới về điện máy',
                'summary' => 'Tóm tắt bài viết',
                'content' => 'Nội dung bài viết rất dài và hữu ích về các thiết bị gia dụng tivi tủ lạnh máy giặt đồ điện tử gia đình.',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'is_banned' => true,
            ]);
    }

    /**
     * Test chức năng Trợ lý SEO AI bài viết phát hiện hành vi spam và tự động cấm.
     */
    public function test_ai_assist_spam_detection()
    {
        // Gửi trùng lặp 4 lần
        for ($i = 0; $i < 4; $i++) {
            $response = $this->actingAs($this->user)
                ->postJson('/lifestyle/ai-assist', [
                    'title' => 'Tiêu đề bài viết spam',
                    'summary' => 'Tóm tắt bài viết spam',
                    'content' => 'Nội dung bài viết spam lặp đi lặp lại rất nhiều lần trên hệ thống.',
                ]);
            
            if ($i < 3) {
                $this->assertNotEquals(403, $response->getStatusCode());
            } else {
                $response->assertStatus(403)
                    ->assertJson([
                        'success' => false,
                        'is_banned' => true,
                    ]);
                
                $this->user->refresh();
                $this->assertNotNull($this->user->chatbot_banned_until);
            }
        }
    }

    /**
     * Test quy trình đặt lịch sửa chữa có trạng thái (Stateful Repair Booking Flow).
     */
    public function test_stateful_repair_booking_flow()
    {
        // 1. Gửi tin nhắn đầu tiên: Yêu cầu đặt lịch chung chung (Chưa đủ thông tin)
        $response1 = $this->actingAs($this->user)
            ->postJson('/chatbot', [
                'prompt' => 'đặt tôi lịch sửa chữa máy tính lúc 10h ngày mai',
            ]);

        $response1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_repair_form' => false,
            ]);

        $this->assertTrue(session()->get('repair_booking_in_progress'));
        $this->assertNotNull(session()->get('repair_booking_data'));

        // 2. Gửi tin nhắn thứ hai: Cung cấp thêm dòng máy
        $response2 = $this->actingAs($this->user)
            ->postJson('/chatbot', [
                'prompt' => 'Laptop ASUS Vivobook 15 A515EA',
            ]);

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_repair_form' => false,
            ]);

        $this->assertEquals('Laptop ASUS Vivobook 15 A515EA', session()->get('repair_booking_data.device_model'));
        $this->assertTrue(session()->get('repair_booking_in_progress'));

        // 3. Gửi tin nhắn thứ ba: Mô tả tình trạng lỗi -> Hoàn tất và hiển thị Form Card
        $response3 = $this->actingAs($this->user)
            ->postJson('/chatbot', [
                'prompt' => 'máy bị sọc màn hình nhấp nháy liên tục',
            ]);

        $response3->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_repair_form' => true,
            ])
            ->assertJsonPath('default_data.issue_desc', 'Laptop ASUS Vivobook 15 A515EA - máy bị sọc màn hình nhấp nháy liên tục');

        // Hệ thống phải dọn dẹp các session sau khi hoàn thành
        $this->assertNull(session()->get('repair_booking_in_progress'));
        $this->assertNull(session()->get('repair_booking_data'));
    }

    /**
     * Test cảnh báo khi lần đầu sử dụng từ ngữ công kích/tục tĩu.
     */
    public function test_abusive_language_warning_on_first_offense()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/chatbot', [
                'prompt' => 'Thằng AI này ngu vcl',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_abusive' => true,
            ]);

        $this->assertEquals(1, session()->get('chatbot_abusive_warning_count'));
    }

    /**
     * Test tự động khóa tài khoản khi sử dụng từ ngữ công kích/tục tĩu lần thứ 2.
     */
    public function test_abusive_language_ban_on_second_offense()
    {
        // Lần 1
        $this->actingAs($this->user)
            ->postJson('/chatbot', [
                'prompt' => 'Thằng AI này ngu vcl',
            ]);

        // Lần 2
        $response = $this->actingAs($this->user)
            ->postJson('/chatbot', [
                'prompt' => 'mày là đồ ngu xuẩn cút đi',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'is_banned' => true,
            ]);

        $this->user->refresh();
        $this->assertNotNull($this->user->chatbot_banned_until);
        $this->assertNull(session()->get('chatbot_abusive_warning_count'));
    }
}
