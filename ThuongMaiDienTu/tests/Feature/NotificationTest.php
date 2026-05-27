<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\InventoryItem;
use App\Services\NotificationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user1;
    private User $user2;
    private User $admin;
    private NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        // Chạy seeder để khởi tạo các vai trò (roles)
        $this->seed(RoleSeeder::class);

        // Tạo người dùng mẫu
        $this->user1 = User::create([
            'role_id' => 3, // Khách hàng
            'full_name' => 'Nguyen Van A',
            'email' => 'user1@example.com',
            'password_hash' => bcrypt('password'),
        ]);

        $this->user2 = User::create([
            'role_id' => 3, // Khách hàng
            'full_name' => 'Tran Thi B',
            'email' => 'user2@example.com',
            'password_hash' => bcrypt('password'),
        ]);

        $this->admin = User::create([
            'role_id' => 1, // Admin
            'full_name' => 'Admin System',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('password'),
        ]);

        $this->notificationService = app(NotificationService::class);
    }

    /**
     * Test API GET /notifications/unread-count
     */
    public function test_get_unread_count(): void
    {
        // Ban đầu chưa có thông báo
        $response = $this->actingAs($this->user1)->get(route('notifications.unread-count'));
        $response->assertStatus(200);
        $response->assertJson(['unread_count' => 0]);

        // Tạo 3 thông báo chưa đọc cho user1
        $this->notificationService->createForUser($this->user1, [
            'type' => 'promotion.auto',
            'title' => 'Khuyến mãi 1',
            'content' => 'Nội dung khuyến mãi 1',
        ]);
        $this->notificationService->createForUser($this->user1, [
            'type' => 'order.created',
            'title' => 'Đơn hàng mới',
            'content' => 'Đơn hàng của bạn đã được tiếp nhận',
        ]);
        $this->notificationService->createForUser($this->user2, [
            'type' => 'order.created',
            'title' => 'Đơn hàng user2',
            'content' => 'Nội dung khác',
        ]);

        // Kiểm tra lại
        $response = $this->actingAs($this->user1)->get(route('notifications.unread-count'));
        $response->assertStatus(200);
        $response->assertJson(['unread_count' => 2]);
    }

    /**
     * Test PATCH /notifications/{notification}/read cho phép đánh dấu đã đọc của chính mình
     */
    public function test_mark_own_notification_as_read(): void
    {
        $notif = $this->notificationService->createForUser($this->user1, [
            'type' => 'order.created',
            'title' => 'Đơn hàng mới',
            'content' => 'Đơn hàng của bạn đã được tiếp nhận',
        ]);

        $this->assertNull($notif->read_at);

        // Gọi API dưới dạng JSON
        $response = $this->actingAs($this->user1)
            ->patchJson(route('notifications.read', $notif->notification_id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $notif->refresh();
        $this->assertNotNull($notif->read_at);
    }

    /**
     * Test PATCH /notifications/{notification}/read của người khác -> trả về 403
     */
    public function test_mark_other_notification_as_read_fails(): void
    {
        $notifOfUser2 = $this->notificationService->createForUser($this->user2, [
            'type' => 'order.created',
            'title' => 'Đơn hàng user2',
            'content' => 'Nội dung',
        ]);

        // user1 cố tình đánh dấu đã đọc thông báo của user2
        $response = $this->actingAs($this->user1)
            ->patchJson(route('notifications.read', $notifOfUser2->notification_id));

        $response->assertStatus(403);

        // Đảm bảo thông báo chưa bị đọc
        $notifOfUser2->refresh();
        $this->assertNull($notifOfUser2->read_at);
    }

    /**
     * Test POST /notifications/read-all đánh dấu tất cả thông báo của mình là đã đọc
     */
    public function test_mark_all_as_read(): void
    {
        $this->notificationService->createForUser($this->user1, [
            'type' => 'promotion.auto',
            'title' => 'KM 1',
            'content' => 'ND 1',
        ]);
        $this->notificationService->createForUser($this->user1, [
            'type' => 'promotion.auto',
            'title' => 'KM 2',
            'content' => 'ND 2',
        ]);
        $notifOfUser2 = $this->notificationService->createForUser($this->user2, [
            'type' => 'promotion.auto',
            'title' => 'KM U2',
            'content' => 'ND U2',
        ]);

        $response = $this->actingAs($this->user1)
            ->postJson(route('notifications.read-all'));

        $response->assertStatus(200);

        // Kiểm tra số lượng chưa đọc của user1
        $this->assertEquals(0, $this->notificationService->unreadCountForUser($this->user1));

        // Thông báo của user2 vẫn chưa bị đọc
        $notifOfUser2->refresh();
        $this->assertNull($notifOfUser2->read_at);
    }

    /**
     * Test chèn hàng loạt (bulk insert) createForUsers trong NotificationService
     */
    public function test_create_for_users_bulk_insert(): void
    {
        $users = collect([$this->user1, $this->user2]);
        $payload = [
            'type' => 'promotion.auto',
            'title' => 'Đợt KM Lớn',
            'content' => 'Giảm giá 50% cho tất cả sản phẩm',
            'action_url' => '/promo',
            'data' => ['coupon' => 'SUMMER50'],
        ];

        $count = $this->notificationService->createForUsers($users, $payload);
        $this->assertEquals(2, $count);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user1->user_id,
            'title' => 'Đợt KM Lớn',
            'action_url' => '/promo',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user2->user_id,
            'title' => 'Đợt KM Lớn',
            'action_url' => '/promo',
        ]);

        // Kiểm tra dữ liệu JSON trong DB được decode đúng
        $notif = Notification::where('user_id', $this->user1->user_id)->first();
        $this->assertEquals(['coupon' => 'SUMMER50'], $notif->data);
    }

    /**
     * Test createForUsers thiếu trường bắt buộc gây ra Undefined Key hoặc SQL error
     */
    public function test_create_for_users_missing_fields_throws_exception(): void
    {
        $users = collect([$this->user1]);
        $invalidPayload = [
            'title' => 'Thiếu type',
            // thiếu 'type' và 'content'
        ];

        $this->expectException(\ErrorException::class);
        // Do PHP 8 quăng ErrorException cho undefined array key
        $this->notificationService->createForUsers($users, $invalidPayload);
    }

    /**
     * Test chèn hàng loạt và kiểm tra low stock (lowStockCheck) của Admin
     */
    public function test_low_stock_check(): void
    {
        // 1. Tạo Supplier
        $supplier = Supplier::create([
            'name' => 'Supplier Test LLC',
            'phone' => '0987654321',
            'email' => 'supplier@example.com',
            'address' => '123 Test St',
        ]);

        // 2. Tạo PurchaseOrder
        $po = PurchaseOrder::create([
            'supplier_id' => $supplier->supplier_id,
            'total_cost' => 100000000,
        ]);

        // 3. Tạo Category
        $category = Category::create([
            'name' => 'Smartphone',
            'slug' => 'smartphone',
        ]);

        // 4. Tạo Product
        $product = Product::create([
            'category_id' => $category->category_id,
            'name' => 'iPhone 15 Pro Max Test',
            'base_price' => 30000000,
            'slug' => 'iphone-15-pro-max-test',
        ]);

        // 5. Tạo ProductVariant
        $variant = ProductVariant::create([
            'product_id' => $product->product_id,
            'color' => 'Blue Titanium',
            'rom_capacity' => '256GB',
            'extra_price' => 2000000,
        ]);

        // 6. Tạo 5 InventoryItem (Dưới ngưỡng low stock = 10)
        for ($i = 1; $i <= 5; $i++) {
            InventoryItem::create([
                'variant_id' => $variant->variant_id,
                'po_id' => $po->po_id,
                'imei_serial' => 'IMEI_LOW_STOCK_' . $i,
                'warehouse_loc' => 'Ke A-2',
                'status' => 'In_Stock',
            ]);
        }

        // Gọi action low stock check với quyền Admin
        $response = $this->actingAs($this->admin)
            ->post(route('admin.notifications.low-stock-check'));

        // Phải redirect back về trang quản lý thông báo
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Phải có thông báo tồn kho thấp cho Admin trong DB
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->user_id,
            'type' => 'inventory.low_stock',
            'title' => 'Tồn kho thấp: iPhone 15 Pro Max Test',
        ]);

        $notification = Notification::where('user_id', $this->admin->user_id)
            ->where('type', 'inventory.low_stock')
            ->first();

        // Kiểm tra xem trường data có khớp thông tin chi tiết không
        $data = $notification->data;
        $this->assertEquals($product->product_id, $data['product_id']);
        $this->assertEquals($variant->variant_id, $data['variant_id']);
        $this->assertEquals(5, $data['stock']);
        $this->assertEquals(10, $data['threshold']);
    }
}
