<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    private $role;
    private $user;
    private $password = '123456';

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo vai trò Khách hàng mẫu
        $this->role = Role::create([
            'name' => 'Customer',
            'description' => 'Standard Customer',
        ]);

        // Tạo user mẫu với trạng thái Active
        $this->user = User::create([
            'role_id' => $this->role->role_id,
            'full_name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password_hash' => Hash::make($this->password),
            'status' => 'Active',
            'member_tier' => 'Dong',
            'is_2fa_enabled' => 0,
        ]);
    }

    /**
     * Test đăng nhập thành công với thông tin chính xác.
     */
    public function test_api_login_returns_token_and_user_profile_with_valid_credentials()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@gmail.com',
            'password' => $this->password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'token',
                'user' => [
                    'user_id',
                    'role_id',
                    'full_name',
                    'email',
                    'member_tier',
                    'status',
                ],
            ])
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->user_id,
            'tokenable_type' => User::class,
        ]);
    }

    /**
     * Test đăng nhập thất bại khi sai mật khẩu.
     */
    public function test_api_login_returns_401_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@gmail.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /**
     * Test đăng nhập thất bại do lỗi kiểm định dữ liệu đầu vào.
     */
    public function test_api_login_returns_422_with_validation_errors()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'message',
                'errors',
            ]);
    }

    /**
     * Test đăng nhập thất bại đối với tài khoản bị Banned.
     */
    public function test_api_login_returns_403_for_banned_user()
    {
        $bannedUser = User::create([
            'role_id' => $this->role->role_id,
            'full_name' => 'Banned User',
            'email' => 'banned@gmail.com',
            'password_hash' => Hash::make($this->password),
            'status' => 'Banned',
            'member_tier' => 'Dong',
            'is_2fa_enabled' => 0,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'banned@gmail.com',
            'password' => $this->password,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /**
     * Test lấy thông tin tài khoản đang đăng nhập thành công.
     */
    public function test_api_me_returns_profile_for_authenticated_request()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->getJson('/api/v1/me', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'user' => [
                    'email' => $this->user->email,
                ],
            ]);
    }

    /**
     * Test chặn yêu cầu lấy thông tin tài khoản khi không truyền token hoặc truyền sai token.
     */
    public function test_api_me_returns_401_for_unauthenticated_request()
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    /**
     * Test đăng xuất và vô hiệu hóa session token thành công.
     */
    public function test_api_logout_invalidates_session_token()
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/v1/logout', [], [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->user_id,
        ]);

        // Gửi lại yêu cầu lấy thông tin bằng token vừa xóa sẽ bị chặn 401
        $retryResponse = $this->getJson('/api/v1/me', [
            'Authorization' => "Bearer $token",
        ]);

        $retryResponse->assertStatus(401);
    }
}
