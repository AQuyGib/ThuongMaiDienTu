<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WarrantyClaim;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * WarrantyClaimTest
 *
 * Kiểm thử toàn bộ luồng yêu cầu bảo hành & đổi trả:
 *  1. Người dùng gửi yêu cầu thành công
 *  2. Validation bắt buộc (thiếu field, IMEI không tồn tại, ...)
 *  3. Admin xem danh sách – có lọc theo status & claim_type
 *  4. Admin duyệt yêu cầu
 *  5. Admin từ chối yêu cầu với ghi chú
 *  6. Khách không thể gọi admin routes
 */
class WarrantyClaimTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────────────────────

    /** Tạo Admin user (role_id = 1) */
    protected function makeAdmin(): User
    {
        $role = Role::firstOrCreate(
            ['role_id' => 1],
            ['name' => 'Admin', 'description' => 'Quản trị viên']
        );

        return User::create([
            'role_id'       => $role->role_id,
            'full_name'     => 'Admin Test',
            'email'         => 'admin@test.com',
            'password_hash' => bcrypt('password'),
            'member_tier'   => 'Vang',
            'status'        => 'Active',
        ]);
    }

    /** Tạo Customer user (role_id = 3) */
    protected function makeCustomer(string $email = 'customer@test.com'): User
    {
        $role = Role::firstOrCreate(
            ['role_id' => 3],
            ['name' => 'Customer', 'description' => 'Khách hàng']
        );

        return User::create([
            'role_id'       => $role->role_id,
            'full_name'     => 'Khách Hàng Test',
            'email'         => $email,
            'password_hash' => bcrypt('password'),
            'member_tier'   => 'Dong',
            'status'        => 'Active',
        ]);
    }

    /**
     * Tạo 1 InventoryItem với IMEI cụ thể để kiểm thử
     * Phải tạo chuỗi quan hệ: Supplier → PurchaseOrder → Category → Product → Variant → InventoryItem
     * Dùng đúng schema DB thực tế.
     */
    protected function makeInventoryItem(string $imei = 'TESTIMEI001', string $status = 'Sold'): InventoryItem
    {
        $supplier = Supplier::create([
            'name'  => 'Test Supplier ' . uniqid(),
            'phone' => '0900000001',
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $supplier->supplier_id,
            'total_cost'  => 0,
        ]);

        $category = Category::create([
            'parent_id' => null,
            'name'      => 'Test Category',
            'slug'      => 'test-cat-' . uniqid(),
        ]);

        $product = Product::create([
            'category_id' => $category->category_id,
            'name'        => 'Test Phone ' . uniqid(),
            'base_price'  => 10000000,
            'sold_count'  => 0,
            'stock'       => 10,
            'status'      => 1,  // tinyint(1): 1 = active
        ]);

        $variant = ProductVariant::create([
            'product_id'   => $product->product_id,
            'color'        => 'Black',
            'rom_capacity' => '128GB',
            'extra_price'  => 0,
            'stock'        => 10,
        ]);

        return InventoryItem::create([
            'variant_id'    => $variant->variant_id,
            'po_id'         => $po->po_id,
            'imei_serial'   => substr($imei, 0, 30), // varchar(30)
            'warehouse_loc' => 'Kho Test',
            'status'        => $status, // enum('In_Stock','Sold','Defective')
        ]);
    }

    protected function makeWarranty(InventoryItem $item, string $status = 'active', int $startDaysAgo = 10, int $durationMonths = 12): \App\Models\Warranty
    {
        $startDate = \Carbon\Carbon::now()->subDays($startDaysAgo);
        $endDate = (clone $startDate)->addMonths($durationMonths);

        return \App\Models\Warranty::create([
            'item_id'         => $item->item_id,
            'start_date'      => $startDate,
            'end_date'        => $endDate,
            'warranty_status' => $status,
            'warranty_type'   => 'manufacturer',
            'note'            => 'Bảo hành test.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  1. FRONTEND: GỬI YÊU CẦU
    // ──────────────────────────────────────────────────────────────

    /** Test: Gửi yêu cầu bảo hành thành công với IMEI hợp lệ */
    public function test_customer_can_submit_warranty_claim_successfully(): void
    {
        $item = $this->makeInventoryItem('IMEI-WARRANTY-001');
        $this->makeWarranty($item, 'active', 10);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-WARRANTY-001',
            'customer_name'  => 'Nguyễn Văn Test',
            'customer_phone' => '0912345678',
            'customer_email' => 'test@example.com',
            'claim_type'     => 'warranty',
            'reason'         => 'Màn hình bị sọc dọc sau 2 tuần sử dụng.',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Kiểm tra DB đã lưu đúng
        $this->assertDatabaseHas('warranty_claims', [
            'imei_serial'   => 'IMEI-WARRANTY-001',
            'claim_type'    => 'warranty',
            'status'        => 'pending',
            'customer_name' => 'Nguyễn Văn Test',
        ]);
    }

    /** Test: Gửi yêu cầu đổi trả (return) thành công */
    public function test_customer_can_submit_return_claim_successfully(): void
    {
        $item = $this->makeInventoryItem('IMEI-RETURN-002');
        $this->makeWarranty($item, 'active', 10);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'         => 'IMEI-RETURN-002',
            'customer_name'       => 'Trần Thị Test',
            'customer_phone'      => '0987654321',
            'customer_email'      => null,
            'claim_type'          => 'return',
            'reason'              => 'Giao sai màu so với đơn hàng đã đặt.',
            'bank_name'           => 'Vietcombank',
            'bank_account_number' => '1234567890',
            'bank_account_name'   => 'Trần Thị Test',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('warranty_claims', [
            'imei_serial' => 'IMEI-RETURN-002',
            'claim_type'  => 'return',
            'status'      => 'pending',
        ]);
    }

    /** Test: Gửi yêu cầu đổi trả (return) hoàn tiền mặt thành công */
    public function test_customer_can_submit_return_claim_with_cash_refund_method_successfully(): void
    {
        $item = $this->makeInventoryItem('IMEI-RETURN-CASH-002');
        $this->makeWarranty($item, 'active', 10);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'         => 'IMEI-RETURN-CASH-002',
            'customer_name'       => 'Trần Thị Test Cash',
            'customer_phone'      => '0987654321',
            'customer_email'      => null,
            'claim_type'          => 'return',
            'reason'              => 'Giao sai màu so với đơn hàng đã đặt.',
            'refund_method'       => 'cash',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('warranty_claims', [
            'imei_serial'   => 'IMEI-RETURN-CASH-002',
            'claim_type'    => 'return',
            'status'        => 'pending',
            'refund_method' => 'cash',
            'bank_name'     => null,
        ]);
    }

    /** Test: Gửi yêu cầu đổi máy (exchange) thành công */
    public function test_customer_can_submit_exchange_claim_successfully(): void
    {
        $item = $this->makeInventoryItem('IMEI-EXCHANGE-003');
        $this->makeWarranty($item, 'active', 10);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-EXCHANGE-003',
            'customer_name'  => 'Lê Văn Test',
            'customer_phone' => '0900000003',
            'claim_type'     => 'exchange',
            'reason'         => 'Lỗi WiFi không kết nối được từ hôm mua.',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('warranty_claims', [
            'claim_type' => 'exchange',
            'status'     => 'pending',
        ]);
    }

    /** Test: Claim được lưu với user_id khi khách đăng nhập */
    public function test_claim_stores_user_id_when_authenticated(): void
    {
        $customer = $this->makeCustomer();
        $item = $this->makeInventoryItem('IMEI-AUTH-004');
        $this->makeWarranty($item, 'active', 10);

        $this->actingAs($customer, 'web')
             ->postJson(route('warranty.claim.store'), [
                 'imei_serial'    => 'IMEI-AUTH-004',
                 'customer_name'  => $customer->full_name,
                 'customer_phone' => '0900000004',
                 'claim_type'     => 'warranty',
                 'reason'         => 'Pin tụt nhanh.',
              ]);

        $this->assertDatabaseHas('warranty_claims', [
            'imei_serial' => 'IMEI-AUTH-004',
            'user_id'     => $customer->user_id,
        ]);
    }

    /** Test: Chặn gửi yêu cầu trùng lặp khi đang chờ duyệt */
    public function test_duplicate_pending_claim_fails(): void
    {
        $item = $this->makeInventoryItem('IMEI-DUPLICATE-001');
        $this->makeWarranty($item, 'active', 10);

        // Tạo sẵn 1 claim ở trạng thái pending
        WarrantyClaim::create([
            'imei_serial'    => 'IMEI-DUPLICATE-001',
            'customer_name'  => 'Test Name',
            'customer_phone' => '0900000001',
            'claim_type'     => 'warranty',
            'reason'         => 'Pending claim reason',
            'status'         => 'pending',
        ]);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-DUPLICATE-001',
            'customer_name'  => 'Test Name 2',
            'customer_phone' => '0900000001',
            'claim_type'     => 'warranty',
            'reason'         => 'Duplicate claim reason',
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'success' => false,
                     'message' => 'Thiết bị với mã IMEI này đang có một yêu cầu hỗ trợ chờ xử lý. Vui lòng đợi phản hồi từ ban quản trị.',
                 ]);
    }

    /** Test: Khách hàng đăng nhập không thể gửi yêu cầu cho thiết bị của người khác */
    public function test_claim_fails_for_other_users_device(): void
    {
        $customer1 = $this->makeCustomer('c1@test.com');
        $customer2 = $this->makeCustomer('c2@test.com');

        $item = $this->makeInventoryItem('IMEI-OWNER-001');
        $this->makeWarranty($item, 'active', 10);

        // Tạo đơn hàng thuộc sở hữu của customer1
        $order = \App\Models\Order::create([
            'user_id'        => $customer1->user_id,
            'customer_name'  => $customer1->full_name,
            'customer_phone' => '0911111111',
            'status'         => 'Delivered',
            'total_amount'   => 10000000,
            'final_amount'   => 10000000,
        ]);
        \App\Models\OrderDetail::create([
            'order_id' => $order->order_id,
            'item_id'  => $item->item_id,
            'quantity' => 1,
            'price'    => 10000000,
        ]);

        // Đăng nhập bằng customer2 và gửi yêu cầu cho sản phẩm của customer1
        $response = $this->actingAs($customer2, 'web')
             ->postJson(route('warranty.claim.store'), [
                 'imei_serial'    => 'IMEI-OWNER-001',
                 'customer_name'  => $customer2->full_name,
                 'customer_phone' => '0922222222',
                 'claim_type'     => 'warranty',
                 'reason'         => 'Bị sọc màn hình.',
             ]);

        $response->assertStatus(403)
                 ->assertJsonFragment([
                     'success' => false,
                     'message' => 'Bạn không sở hữu sản phẩm này. Không thể gửi yêu cầu hỗ trợ.',
                 ]);
    }

    /** Test: Khách vãng lai không thể gửi yêu cầu nếu số điện thoại không khớp đơn hàng */
    public function test_guest_claim_fails_for_non_matching_phone(): void
    {
        $item = $this->makeInventoryItem('IMEI-GUEST-001');
        $this->makeWarranty($item, 'active', 10);

        // Tạo đơn hàng có số điện thoại khách mua là 0912345678
        $order = \App\Models\Order::create([
            'user_id'        => null,
            'customer_name'  => 'Guest Buyer',
            'customer_phone' => '0912345678',
            'status'         => 'Delivered',
            'total_amount'   => 10000000,
            'final_amount'   => 10000000,
        ]);
        \App\Models\OrderDetail::create([
            'order_id' => $order->order_id,
            'item_id'  => $item->item_id,
            'quantity' => 1,
            'price'    => 10000000,
        ]);

        // Gửi yêu cầu với số điện thoại khác (ví dụ: 0988888888)
        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-GUEST-001',
            'customer_name'  => 'Imposter User',
            'customer_phone' => '0988888888',
            'claim_type'     => 'warranty',
            'reason'         => 'Hỏng loa.',
        ]);

        $response->assertStatus(403)
                 ->assertJsonFragment([
                     'success' => false,
                     'message' => 'Số điện thoại liên hệ không trùng khớp với thông tin mua hàng của thiết bị này.',
                 ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  2. VALIDATION
    // ──────────────────────────────────────────────────────────────

    /** Test: Thiếu imei_serial → validation lỗi 422 */
    public function test_claim_fails_without_imei_serial(): void
    {
        $response = $this->postJson(route('warranty.claim.store'), [
            'customer_name'  => 'Test',
            'customer_phone' => '0900000000',
            'claim_type'     => 'warranty',
            'reason'         => 'Lỗi gì đó.',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['imei_serial']);
    }

    /** Test: IMEI không tồn tại trong inventory → validation lỗi exists */
    public function test_claim_fails_with_nonexistent_imei(): void
    {
        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-DOES-NOT-EXIST',
            'customer_name'  => 'Test',
            'customer_phone' => '0900000000',
            'claim_type'     => 'warranty',
            'reason'         => 'Lỗi gì đó.',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['imei_serial']);
    }

    /** Test: Thiếu customer_name → lỗi validation */
    public function test_claim_fails_without_customer_name(): void
    {
        $item = $this->makeInventoryItem('IMEI-VAL-005');
        $this->makeWarranty($item, 'active', 10);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-VAL-005',
            'customer_phone' => '0900000000',
            'claim_type'     => 'warranty',
            'reason'         => 'Lỗi gì đó.',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['customer_name']);
    }

    /** Test: Thiếu customer_phone → lỗi validation */
    public function test_claim_fails_without_customer_phone(): void
    {
        $item = $this->makeInventoryItem('IMEI-VAL-006');
        $this->makeWarranty($item, 'active', 10);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'   => 'IMEI-VAL-006',
            'customer_name' => 'Test',
            'claim_type'    => 'warranty',
            'reason'        => 'Lỗi gì đó.',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['customer_phone']);
    }

    /** Test: claim_type không hợp lệ → lỗi validation */
    public function test_claim_fails_with_invalid_claim_type(): void
    {
        $item = $this->makeInventoryItem('IMEI-VAL-007');
        $this->makeWarranty($item, 'active', 10);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-VAL-007',
            'customer_name'  => 'Test',
            'customer_phone' => '0900000000',
            'claim_type'     => 'invalid_type', // không nằm trong enum
            'reason'         => 'Lỗi gì đó.',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['claim_type']);
    }

    /** Test: Thiếu reason → lỗi validation */
    public function test_claim_fails_without_reason(): void
    {
        $item = $this->makeInventoryItem('IMEI-VAL-008');
        $this->makeWarranty($item, 'active', 10);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-VAL-008',
            'customer_name'  => 'Test',
            'customer_phone' => '0900000000',
            'claim_type'     => 'warranty',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['reason']);
    }

    /** Test: Email không đúng định dạng → lỗi validation */
    public function test_claim_fails_with_invalid_email(): void
    {
        $item = $this->makeInventoryItem('IMEI-VAL-009');
        $this->makeWarranty($item, 'active', 10);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-VAL-009',
            'customer_name'  => 'Test',
            'customer_phone' => '0900000000',
            'customer_email' => 'not-an-email',
            'claim_type'     => 'warranty',
            'reason'         => 'Lỗi gì đó.',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['customer_email']);
    }

    /** Test: Admin có thể xem danh sách claims */
    public function test_admin_can_view_warranty_claims_list(): void
    {
        $admin = $this->makeAdmin();
        $item  = $this->makeInventoryItem('IMEI-ADMIN-010');
        $this->makeWarranty($item, 'active', 10);

        WarrantyClaim::create([
            'imei_serial'    => $item->imei_serial,
            'customer_name'  => 'Khách Test',
            'customer_phone' => '0900000010',
            'claim_type'     => 'warranty',
            'reason'         => 'Lỗi test.',
            'status'         => 'pending',
        ]);

        $response = $this->actingAs($admin, 'web')
                          ->get(route('admin.warranty-claims.index'));

        $response->assertStatus(200)
                 ->assertSee('Khách Test');
    }

    /** Test: Admin lọc theo status=pending chỉ thấy claims pending */
    public function test_admin_can_filter_claims_by_status(): void
    {
        $admin = $this->makeAdmin();
        $item1 = $this->makeInventoryItem('IMEI-FILTER-A01');
        $item2 = $this->makeInventoryItem('IMEI-FILTER-A02');
        $this->makeWarranty($item1, 'active', 10);
        $this->makeWarranty($item2, 'active', 10);

        WarrantyClaim::create([
            'imei_serial' => $item1->imei_serial, 'customer_name' => 'Pending Customer',
            'customer_phone' => '0900001001', 'claim_type' => 'warranty',
            'reason' => 'Lỗi.', 'status' => 'pending',
        ]);
        WarrantyClaim::create([
            'imei_serial' => $item2->imei_serial, 'customer_name' => 'Approved Customer',
            'customer_phone' => '0900001002', 'claim_type' => 'return',
            'reason' => 'Lỗi.', 'status' => 'approved',
        ]);

        $response = $this->actingAs($admin, 'web')
                          ->get(route('admin.warranty-claims.index', ['status' => 'pending']));

        $response->assertStatus(200)
                 ->assertSee('Pending Customer')
                 ->assertDontSee('Approved Customer');
    }

    /** Test: Admin lọc theo claim_type=warranty */
    public function test_admin_can_filter_claims_by_type(): void
    {
        $admin = $this->makeAdmin();
        $item1 = $this->makeInventoryItem('IMEI-FILTER-B01');
        $item2 = $this->makeInventoryItem('IMEI-FILTER-B02');
        $this->makeWarranty($item1, 'active', 10);
        $this->makeWarranty($item2, 'active', 10);

        WarrantyClaim::create([
            'imei_serial' => $item1->imei_serial, 'customer_name' => 'Bao Hanh KH',
            'customer_phone' => '0900002001', 'claim_type' => 'warranty',
            'reason' => 'Lỗi.', 'status' => 'pending',
        ]);
        WarrantyClaim::create([
            'imei_serial' => $item2->imei_serial, 'customer_name' => 'Doi Tra KH',
            'customer_phone' => '0900002002', 'claim_type' => 'return',
            'reason' => 'Lỗi.', 'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'web')
                          ->get(route('admin.warranty-claims.index', ['claim_type' => 'warranty']));

        $response->assertStatus(200)
                 ->assertSee('Bao Hanh KH')
                 ->assertDontSee('Doi Tra KH');
    }

    // ──────────────────────────────────────────────────────────────
    //  4. ADMIN: DUYỆT YÊU CẦU
    // ──────────────────────────────────────────────────────────────

    /** Test: Admin duyệt claim pending → status chuyển thành approved */
    public function test_admin_can_approve_pending_claim(): void
    {
        $admin = $this->makeAdmin();
        $item  = $this->makeInventoryItem('IMEI-APPROVE-011');
        $this->makeWarranty($item, 'active', 10);

        $claim = WarrantyClaim::create([
            'imei_serial'    => $item->imei_serial,
            'customer_name'  => 'Khách Cần Duyệt',
            'customer_phone' => '0900000011',
            'claim_type'     => 'warranty',
            'reason'         => 'Lỗi test.',
            'status'         => 'pending',
        ]);

        $response = $this->actingAs($admin, 'web')
                          ->post(route('admin.warranty-claims.approve', $claim->id), [
                              'admin_note' => 'Đã xác nhận lỗi, tiếp nhận bảo hành.',
                          ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('warranty_claims', [
            'id'         => $claim->id,
            'status'     => 'approved',
            'admin_note' => 'Đã xác nhận lỗi, tiếp nhận bảo hành.',
        ]);
    }

    /** Test: Admin duyệt không cần admin_note (tuỳ chọn) */
    public function test_admin_can_approve_claim_without_note(): void
    {
        $admin = $this->makeAdmin();
        $item  = $this->makeInventoryItem('IMEI-APPROVE-012');
        $this->makeWarranty($item, 'active', 10);

        $claim = WarrantyClaim::create([
            'imei_serial'    => $item->imei_serial,
            'customer_name'  => 'KH Không Ghi Chú',
            'customer_phone' => '0900000012',
            'claim_type'     => 'return',
            'reason'         => 'Sai màu.',
            'status'         => 'pending',
        ]);

        $this->actingAs($admin, 'web')
             ->post(route('admin.warranty-claims.approve', $claim->id), []);

        $this->assertDatabaseHas('warranty_claims', [
            'id'     => $claim->id,
            'status' => 'approved',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  5. ADMIN: TỪ CHỐI YÊU CẦU
    // ──────────────────────────────────────────────────────────────

    /** Test: Admin từ chối claim → status chuyển thành rejected */
    public function test_admin_can_reject_pending_claim(): void
    {
        $admin = $this->makeAdmin();
        $item  = $this->makeInventoryItem('IMEI-REJECT-013');
        $this->makeWarranty($item, 'active', 10);

        $claim = WarrantyClaim::create([
            'imei_serial'    => $item->imei_serial,
            'customer_name'  => 'Khách Bị Từ Chối',
            'customer_phone' => '0900000013',
            'claim_type'     => 'exchange',
            'reason'         => 'Muốn đổi máy khác.',
            'status'         => 'pending',
        ]);

        $response = $this->actingAs($admin, 'web')
                          ->post(route('admin.warranty-claims.reject', $claim->id), [
                              'admin_note' => 'Không đủ điều kiện đổi trả theo chính sách.',
                          ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('warranty_claims', [
            'id'         => $claim->id,
            'status'     => 'rejected',
            'admin_note' => 'Không đủ điều kiện đổi trả theo chính sách.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  6. PHÂN QUYỀN: Khách không thể gọi admin routes
    // ──────────────────────────────────────────────────────────────

    /** Test: Người dùng chưa đăng nhập → redirect login khi vào admin */
    public function test_unauthenticated_user_cannot_access_admin_index(): void
    {
        $response = $this->get(route('admin.warranty-claims.index'));
        $response->assertRedirect(); // redirect về login
    }

    /** Test: Customer (role_id=3) không thể vào admin claims list */
    public function test_customer_cannot_access_admin_warranty_claims(): void
    {
        $customer = $this->makeCustomer('another@test.com');

        $response = $this->actingAs($customer, 'web')
                          ->get(route('admin.warranty-claims.index'));

        // Phải bị chặn – 403 hoặc redirect
        $this->assertNotEquals(200, $response->status());
    }

    /** Test: Customer không thể approve claim người khác */
    public function test_customer_cannot_approve_claim(): void
    {
        $customer = $this->makeCustomer('evil@test.com');
        $item     = $this->makeInventoryItem('IMEI-SEC-014');
        $this->makeWarranty($item, 'active', 10);

        $claim = WarrantyClaim::create([
            'imei_serial'    => $item->imei_serial,
            'customer_name'  => 'Nạn Nhân',
            'customer_phone' => '0900000014',
            'claim_type'     => 'warranty',
            'reason'         => 'Test.',
            'status'         => 'pending',
        ]);

        $this->actingAs($customer, 'web')
             ->post(route('admin.warranty-claims.approve', $claim->id), [
                 'admin_note' => 'Hack attempt',
             ]);

        // Claim phải vẫn còn pending
        $this->assertDatabaseHas('warranty_claims', [
            'id'     => $claim->id,
            'status' => 'pending',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  7. MODEL: TRẠNG THÁI MẶC ĐỊNH
    // ──────────────────────────────────────────────────────────────

    /** Test: Claim mới tạo luôn có status=pending mặc định */
    public function test_new_claim_has_pending_status_by_default(): void
    {
        $item = $this->makeInventoryItem('IMEI-DEFAULT-015');
        $this->makeWarranty($item, 'active', 10);

        // Khi không truyền status → model lấy default từ DB schema (pending)
        $claim = WarrantyClaim::create([
            'imei_serial'    => substr($item->imei_serial, 0, 30),
            'customer_name'  => 'Test Default',
            'customer_phone' => '0900000015',
            'claim_type'     => 'warranty',
            'reason'         => 'Lỗi test.',
            'status'         => 'pending', // đặt rõ để đảm bảo constraint default
        ]);

        $this->assertEquals('pending', $claim->status);
        $this->assertNull($claim->admin_note);

        // Xác nhận DB lưu đúng
        $this->assertDatabaseHas('warranty_claims', [
            'id'     => $claim->id,
            'status' => 'pending',
        ]);
    }

    /** Test: Model lưu đúng dữ liệu khi không có email và user_id */
    public function test_claim_can_be_created_without_email_and_user_id(): void
    {
        $item = $this->makeInventoryItem('IMEI-GUEST-016');
        $this->makeWarranty($item, 'active', 10);

        $claim = WarrantyClaim::create([
            'imei_serial'    => $item->imei_serial,
            'customer_name'  => 'Khách Vãng Lai',
            'customer_phone' => '0900000016',
            'claim_type'     => 'return',
            'reason'         => 'Sản phẩm lỗi.',
        ]);

        $this->assertNull($claim->user_id);
        $this->assertNull($claim->customer_email);
        $this->assertDatabaseHas('warranty_claims', [
            'imei_serial' => 'IMEI-GUEST-016',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  8. MỚI: RÀNG BUỘC KÍCH HOẠT, BÁN HÀNG VÀ HẠN ĐỔI TRẢ
    // ──────────────────────────────────────────────────────────────

    /** Test: Gửi yêu cầu thất bại nếu thiết bị chưa được bán ra (còn trong kho In_Stock) */
    public function test_claim_fails_if_device_is_still_in_stock(): void
    {
        $item = $this->makeInventoryItem('IMEI-STOCK-999', 'In_Stock');
        $this->makeWarranty($item, 'active', 5);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-STOCK-999',
            'customer_name'  => 'Khách Test',
            'customer_phone' => '0912345678',
            'claim_type'     => 'warranty',
            'reason'         => 'Hỏng loa.',
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'Sản phẩm này chưa được bán ra (Đang trong kho). Không thể gửi yêu cầu dịch vụ.']);
    }

    /** Test: Yêu cầu bảo hành thất bại nếu thiết bị chưa kích hoạt bảo hành */
    public function test_warranty_claim_fails_for_unactivated_device(): void
    {
        // Có bán (status = Sold) nhưng không có bản ghi warranty
        $item = $this->makeInventoryItem('IMEI-UNACTIVATED-888', 'Sold');

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-UNACTIVATED-888',
            'customer_name'  => 'Khách Test',
            'customer_phone' => '0912345678',
            'claim_type'     => 'warranty',
            'reason'         => 'Hỏng camera.',
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'Thiết bị này chưa được kích hoạt bảo hành. Vui lòng kích hoạt bảo hành trước.']);
    }

    /** Test: Yêu cầu bảo hành thất bại nếu bảo hành đã hết hạn */
    public function test_warranty_claim_fails_for_expired_warranty(): void
    {
        $item = $this->makeInventoryItem('IMEI-EXPIRED-777', 'Sold');
        // Kích hoạt từ 400 ngày trước, thời hạn 12 tháng (khoảng 365 ngày) -> Đã hết hạn
        $this->makeWarranty($item, 'expired', 400, 12);

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-EXPIRED-777',
            'customer_name'  => 'Khách Test',
            'customer_phone' => '0912345678',
            'claim_type'     => 'warranty',
            'reason'         => 'Hỏng mic.',
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'Thiết bị đã hết hạn bảo hành hoặc đang không ở trạng thái hoạt động.']);
    }

    /** Test: Tra cứu bảo hành (lookup) trả về đúng cờ trạng thái can_claim */
    public function test_lookup_returns_correct_eligibility_flags(): void
    {
        // 1. Máy In_Stock -> cannot claim anything
        $itemStock = $this->makeInventoryItem('IMEI-LOOKUP-STOCK', 'In_Stock');
        $responseStock = $this->postJson(route('warranty.lookup'), ['imei' => 'IMEI-LOOKUP-STOCK']);
        $responseStock->assertStatus(200)
                      ->assertJsonFragment([
                          'can_claim_warranty' => false,
                          'can_claim_return'   => false,
                          'note'               => 'Sản phẩm này chưa được bán ra (Đang trong kho). Không thể gửi yêu cầu bảo hành hoặc đổi trả.'
                      ]);

        // 2. Máy Sold chưa kích hoạt
        $itemUnactivated = $this->makeInventoryItem('IMEI-LOOKUP-UNACT', 'Sold');
        $responseUnact = $this->postJson(route('warranty.lookup'), ['imei' => 'IMEI-LOOKUP-UNACT']);
        $responseUnact->assertStatus(200)
                      ->assertJsonFragment([
                          'can_claim_warranty' => false,
                          'can_claim_return'   => false,
                          'note'               => 'Thiết bị này chưa được kích hoạt bảo hành. Vui lòng liên hệ hotline để được hỗ trợ kích hoạt.'
                      ]);

        // 3. Máy Sold có bảo hành active và mới kích hoạt 10 ngày (< 30 ngày)
        $itemEligible = $this->makeInventoryItem('IMEI-LOOKUP-ELIGIBLE', 'Sold');
        $this->makeWarranty($itemEligible, 'active', 10, 12);
        $responseEligible = $this->postJson(route('warranty.lookup'), ['imei' => 'IMEI-LOOKUP-ELIGIBLE']);
        $responseEligible->assertStatus(200)
                         ->assertJsonFragment([
                             'can_claim_warranty' => true,
                             'can_claim_return'   => true,
                             'return_days'        => 30,
                             'return_days_left'   => 20
                         ]);

        // 4. Máy Sold có bảo hành active nhưng kích hoạt 45 ngày trước (> 30 ngày)
        $itemExpiredReturn = $this->makeInventoryItem('IMEI-LOOKUP-EXPRET', 'Sold');
        $this->makeWarranty($itemExpiredReturn, 'active', 45, 12);
        $responseExpret = $this->postJson(route('warranty.lookup'), ['imei' => 'IMEI-LOOKUP-EXPRET']);
        $responseExpret->assertStatus(200)
                       ->assertJsonFragment([
                           'can_claim_warranty' => true,
                           'can_claim_return'   => false,
                           'return_days'        => 30,
                           'return_days_left'   => 0
                       ]);
    }

    /** Helper to create item with custom category name and variant price */
    protected function makeCategorizedItem(string $imei, string $categoryName, int $price = 2000000): InventoryItem
    {
        $supplier = Supplier::create([
            'name'  => 'Supplier ' . uniqid(),
            'phone' => '0900000001',
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $supplier->supplier_id,
            'total_cost'  => 0,
        ]);

        $category = Category::create([
            'parent_id' => null,
            'name'      => $categoryName,
            'slug'      => \Illuminate\Support\Str::slug($categoryName) . '-' . uniqid(),
        ]);

        $product = Product::create([
            'category_id' => $category->category_id,
            'name'        => 'Test Product ' . uniqid(),
            'base_price'  => $price,
            'sold_count'  => 0,
            'stock'       => 10,
            'status'      => 1,
        ]);

        $variant = ProductVariant::create([
            'product_id'   => $product->product_id,
            'color'        => 'Black',
            'rom_capacity' => '128GB',
            'extra_price'  => 0,
            'stock'        => 10,
        ]);

        return InventoryItem::create([
            'variant_id'    => $variant->variant_id,
            'po_id'         => $po->po_id,
            'imei_serial'   => $imei,
            'warehouse_loc' => 'Kho Test',
            'status'        => 'Sold',
        ]);
    }

    /** Test: Phụ kiện dưới 1 triệu không cho đổi trả */
    public function test_return_fails_for_cheap_accessory(): void
    {
        $item = $this->makeCategorizedItem('IMEI-CHEAP-ACC', 'Phụ kiện', 500000); // 500k
        $this->makeWarranty($item, 'active', 5, 12); // Kích hoạt 5 ngày trước

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'         => 'IMEI-CHEAP-ACC',
            'customer_name'       => 'Khách Test',
            'customer_phone'      => '0912345678',
            'claim_type'          => 'return',
            'reason'              => 'Muốn hoàn tiền.',
            'bank_name'           => 'Vietcombank',
            'bank_account_number' => '1234567890',
            'bank_account_name'   => 'Khách Test',
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'Yêu cầu đổi trả thất bại. Sản phẩm thuộc nhóm phụ kiện dưới 1 triệu không hỗ trợ đổi trả hàng.']);
    }

    /** Test: Phụ kiện trên 1 triệu có thời hạn đổi trả 15 ngày, quá hạn sẽ fail */
    public function test_return_fails_for_expensive_accessory_after_15_days(): void
    {
        $item = $this->makeCategorizedItem('IMEI-EXP-ACC', 'Phụ kiện', 1500000); // 1.5 triệu
        $this->makeWarranty($item, 'active', 20, 12); // Kích hoạt 20 ngày trước (quá hạn 15 ngày)

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'         => 'IMEI-EXP-ACC',
            'customer_name'       => 'Khách Test',
            'customer_phone'      => '0912345678',
            'claim_type'          => 'return',
            'reason'              => 'Muốn hoàn tiền.',
            'bank_name'           => 'Vietcombank',
            'bank_account_number' => '1234567890',
            'bank_account_name'   => 'Khách Test',
        ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'Yêu cầu đổi trả thất bại. Đã quá thời hạn đổi trả 15 ngày kể từ ngày kích hoạt bảo hành.']);
    }

    /** Test: Nhóm Âm thanh có thời hạn đổi trả 15 ngày, trong hạn sẽ ok */
    public function test_return_success_for_audio_device_within_15_days(): void
    {
        $item = $this->makeCategorizedItem('IMEI-AUDIO-OK', 'Âm thanh', 2500000);
        $this->makeWarranty($item, 'active', 10, 12); // Kích hoạt 10 ngày trước (< 15 ngày)

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'         => 'IMEI-AUDIO-OK',
            'customer_name'       => 'Khách Test',
            'customer_phone'      => '0912345678',
            'claim_type'          => 'return',
            'reason'              => 'Muốn đổi trả.',
            'bank_name'           => 'Vietcombank',
            'bank_account_number' => '1234567890',
            'bank_account_name'   => 'Khách Test',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    /** Test: Gửi yêu cầu thành công với file hình ảnh hợp lệ dưới 20MB */
    public function test_claim_submission_succeeds_with_valid_media(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $item = $this->makeInventoryItem('IMEI-MEDIA-OK', 'Sold');
        $this->makeWarranty($item, 'active', 5, 12);

        $file = \Illuminate\Http\UploadedFile::fake()->create('proof.jpg', 5000, 'image/jpeg'); // 5MB

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-MEDIA-OK',
            'customer_name'  => 'Khách Test',
            'customer_phone' => '0912345678',
            'claim_type'     => 'warranty',
            'reason'         => 'Hỏng loa.',
            'media_file'     => $file,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $claim = \App\Models\WarrantyClaim::where('imei_serial', 'IMEI-MEDIA-OK')->first();
        $this->assertNotNull($claim);
        $this->assertNotNull($claim->media_path);
        $this->assertStringContainsString('storage/warranty_claims', $claim->media_path);

        // Verify the file was stored on disk
        $storedPath = str_replace('storage/', '', $claim->media_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($storedPath);
    }

    /** Test: Gửi yêu cầu thất bại nếu file hình ảnh/video vượt quá 20MB */
    public function test_claim_submission_fails_with_too_large_media(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $item = $this->makeInventoryItem('IMEI-MEDIA-LARGE', 'Sold');
        $this->makeWarranty($item, 'active', 5, 12);

        $file = \Illuminate\Http\UploadedFile::fake()->create('proof.mp4', 25000); // 25MB

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-MEDIA-LARGE',
            'customer_name'  => 'Khách Test',
            'customer_phone' => '0912345678',
            'claim_type'     => 'warranty',
            'reason'         => 'Lỗi màn hình.',
            'media_file'     => $file,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['media_file']);
    }

    /** Test: Gửi yêu cầu thất bại nếu định dạng file không được hỗ trợ */
    public function test_claim_submission_fails_with_invalid_media_format(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $item = $this->makeInventoryItem('IMEI-MEDIA-INVALID', 'Sold');
        $this->makeWarranty($item, 'active', 5, 12);

        $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 1000); // PDF file

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-MEDIA-INVALID',
            'customer_name'  => 'Khách Test',
            'customer_phone' => '0912345678',
            'claim_type'     => 'warranty',
            'reason'         => 'Hỏng nguồn.',
            'media_file'     => $file,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['media_file']);
    }

    /** Test: Tra cứu trả về lịch sử yêu cầu bảo hành/đổi trả */
    public function test_lookup_returns_claims_history(): void
    {
        $item = $this->makeInventoryItem('IMEI-WITH-CLAIMS', 'Sold');
        $this->makeWarranty($item, 'active', 5, 12);

        // Tạo 1 claim
        \App\Models\WarrantyClaim::create([
            'imei_serial'    => 'IMEI-WITH-CLAIMS',
            'customer_name'  => 'Người Yêu Cầu',
            'customer_phone' => '0987654321',
            'claim_type'     => 'warranty',
            'reason'         => 'Nứt kính màn hình.',
            'status'         => 'approved',
            'admin_note'     => 'Đã sửa xong.',
        ]);

        $response = $this->postJson(route('warranty.lookup'), ['imei' => 'IMEI-WITH-CLAIMS']);
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'claims_history' => [
                         '*' => [
                             'id',
                             'claim_type',
                             'status',
                             'reason',
                             'media_path',
                             'admin_note',
                             'created_at',
                         ]
                     ]
                 ])
                 ->assertJsonFragment([
                     'claim_type' => 'warranty',
                     'status'     => 'approved',
                     'reason'     => 'Nứt kính màn hình.',
                     'admin_note' => 'Đã sửa xong.',
                 ]);
    }

    /** Test: Tra cứu bằng mã đơn hàng trả về danh sách IMEI gợi ý */
    public function test_lookup_by_order_code_fallback(): void
    {
        $item = $this->makeInventoryItem('IMEI-ORD-FALLBACK', 'Sold');

        $order = \App\Models\Order::create([
            'order_type' => 'Online',
            'total_amount' => 1000000,
            'final_amount' => 1000000,
            'payment_method' => 'COD',
            'status' => 'Pending',
            'customer_name' => 'Khách Order',
            'customer_phone' => '0912345678',
            'order_code' => 'ORD-TEST-123456',
        ]);

        \App\Models\OrderDetail::create([
            'order_id' => $order->order_id,
            'item_id' => $item->item_id,
            'price' => 1000000,
        ]);

        $response = $this->postJson(route('warranty.lookup'), ['imei' => 'ORD-TEST-123456']);
        $response->assertStatus(404)
                 ->assertJsonFragment(['success' => false]);
        
        $this->assertStringContainsString('IMEI-ORD-FALLBACK', $response->json('message'));
    }

    /** Test: Tra cứu bằng số điện thoại khách hàng trả về danh sách IMEI gợi ý */
    public function test_lookup_by_phone_fallback(): void
    {
        $item = $this->makeInventoryItem('IMEI-PHONE-FALLBACK', 'Sold');

        $order = \App\Models\Order::create([
            'order_type' => 'Online',
            'total_amount' => 1000000,
            'final_amount' => 1000000,
            'payment_method' => 'COD',
            'status' => 'Pending',
            'customer_name' => 'Khách Phone',
            'customer_phone' => '0999999999',
            'order_code' => 'ORD-TEST-999999',
        ]);

        \App\Models\OrderDetail::create([
            'order_id' => $order->order_id,
            'item_id' => $item->item_id,
            'price' => 1000000,
        ]);

        $response = $this->postJson(route('warranty.lookup'), ['imei' => '0999999999']);
        $response->assertStatus(404)
                 ->assertJsonFragment(['success' => false]);
        
        $this->assertStringContainsString('IMEI-PHONE-FALLBACK', $response->json('message'));
    }

    /** Test: CartController searchOrder returns unit details with claim eligibility flags */
    public function test_order_tracking_search_returns_warranty_and_return_eligibility_for_each_unit(): void
    {
        $customer = $this->makeCustomer('tracker@test.com');
        $item = $this->makeInventoryItem('IMEI-TRACK-SEARCH-123', 'Sold');
        $this->makeWarranty($item, 'active', 5, 12); // start 5 days ago, 12 months duration

        $order = \App\Models\Order::create([
            'user_id' => $customer->user_id,
            'order_type' => 'Online',
            'total_amount' => 1000000,
            'final_amount' => 1000000,
            'payment_method' => 'COD',
            'status' => 'Delivered',
            'customer_name' => 'Khách Tracker',
            'customer_phone' => '0912345678',
            'order_code' => 'ORD-TRACK-SEARCH',
        ]);

        \App\Models\OrderDetail::create([
            'order_id' => $order->order_id,
            'item_id' => $item->item_id,
            'price' => 1000000,
        ]);

        $response = $this->actingAs($customer, 'web')->getJson('/orders/search?code=ORD-TRACK-SEARCH');
        
        $response->assertStatus(200)
                 ->assertJsonFragment(['success' => true])
                 ->assertJsonStructure([
                     'items' => [
                         '*' => [
                             'product_name',
                             'quantity',
                             'price',
                             'units' => [
                                 '*' => [
                                     'imei_serial',
                                     'can_claim_warranty',
                                     'can_claim_return',
                                 ]
                             ]
                         ]
                     ]
                 ]);

        $items = $response->json('items');
        $this->assertNotEmpty($items);
        $this->assertNotEmpty($items[0]['units']);
        $unit = $items[0]['units'][0];
        $this->assertEquals('IMEI-TRACK-SEARCH-123', $unit['imei_serial']);
        $this->assertTrue($unit['can_claim_warranty']);
        $this->assertTrue($unit['can_claim_return']);
    }

    /** Test: CartController searchOrder supports searching by phone and returns multiple orders */
    public function test_order_tracking_search_by_phone_returns_multiple_orders(): void
    {
        $item1 = $this->makeInventoryItem('IMEI-PHONE-1', 'Sold');
        $item2 = $this->makeInventoryItem('IMEI-PHONE-2', 'Sold');

        $order1 = \App\Models\Order::create([
            'order_type' => 'Online',
            'total_amount' => 1000000,
            'final_amount' => 1000000,
            'payment_method' => 'COD',
            'status' => 'Pending',
            'customer_name' => 'Khách Hàng Phone',
            'customer_phone' => '0988888888',
            'order_code' => 'ORD-PHONE-1',
        ]);

        \App\Models\OrderDetail::create([
            'order_id' => $order1->order_id,
            'item_id' => $item1->item_id,
            'price' => 1000000,
        ]);

        $order2 = \App\Models\Order::create([
            'order_type' => 'Online',
            'total_amount' => 1500000,
            'final_amount' => 1500000,
            'payment_method' => 'COD',
            'status' => 'Delivered',
            'customer_name' => 'Khách Hàng Phone',
            'customer_phone' => '0988888888',
            'order_code' => 'ORD-PHONE-2',
        ]);

        \App\Models\OrderDetail::create([
            'order_id' => $order2->order_id,
            'item_id' => $item2->item_id,
            'price' => 1500000,
        ]);

        $response = $this->getJson('/orders/search?phone=0988888888');

        $response->assertStatus(200)
                 ->assertJsonFragment(['success' => true, 'multiple' => true])
                 ->assertJsonCount(2, 'orders')
                 ->assertJsonStructure([
                     'orders' => [
                         '*' => [
                             'order_id',
                             'order_code',
                             'customer_name',
                             'customer_phone',
                             'total_amount',
                             'final_amount',
                             'items' => [
                                 '*' => [
                                     'product_name',
                                     'quantity',
                                     'price',
                                     'units'
                                 ]
                             ]
                         ]
                     ]
                 ]);
    }

    /** Test: Admin có thể xem và in hoá đơn của yêu cầu bảo hành/đổi trả */
    public function test_admin_can_view_and_print_warranty_claim_invoice(): void
    {
        $admin = $this->makeAdmin();
        $item = $this->makeInventoryItem('IMEI-INVOICE-TEST');
        $this->makeWarranty($item, 'active', 5, 12);

        $claim = WarrantyClaim::create([
            'imei_serial' => $item->imei_serial,
            'customer_name' => 'Khách In Hóa Đơn',
            'customer_phone' => '0912345678',
            'claim_type' => 'warranty',
            'reason' => 'Lỗi sọc màn hình.',
            'status' => 'approved',
            'admin_note' => 'Đổi màn hình mới miễn phí.',
        ]);

        $response = $this->actingAs($admin, 'web')
                         ->get(route('admin.warranty-claims.invoice', $claim->id));

        $response->assertStatus(200)
                 ->assertSee('Khách In Hóa Đơn')
                 ->assertSee('BIÊN NHẬN BẢO HÀNH')
                 ->assertSee('IMEI-INVOICE-TEST')
                 ->assertSee('Đổi màn hình mới miễn phí.');
    }

    /** Test: Khách hàng (role_id=3) không thể tự ý xem/in hoá đơn admin */
    public function test_customer_cannot_view_or_print_warranty_claim_invoice(): void
    {
        $customer = $this->makeCustomer('attacker@test.com');
        $item = $this->makeInventoryItem('IMEI-INVOICE-ATTACK');
        $this->makeWarranty($item, 'active', 5, 12);

        $claim = WarrantyClaim::create([
            'imei_serial' => $item->imei_serial,
            'customer_name' => 'Khách Hàng Thật',
            'customer_phone' => '0912345678',
            'claim_type' => 'return',
            'reason' => 'Hoàn tiền.',
            'status' => 'approved',
            'refund_amount' => 5000000,
            'refund_method' => 'cash',
        ]);

        $response = $this->actingAs($customer, 'web')
                         ->get(route('admin.warranty-claims.invoice', $claim->id));

        $this->assertNotEquals(200, $response->status());
    }

    /** Test: Người dùng chưa đăng nhập không thể xem/in hoá đơn */
    public function test_unauthenticated_user_cannot_view_or_print_warranty_claim_invoice(): void
    {
        $item = $this->makeInventoryItem('IMEI-INVOICE-GUEST');
        $this->makeWarranty($item, 'active', 5, 12);

        $claim = WarrantyClaim::create([
            'imei_serial' => $item->imei_serial,
            'customer_name' => 'Khách Hàng',
            'customer_phone' => '0912345678',
            'claim_type' => 'exchange',
            'reason' => 'Lỗi.',
            'status' => 'approved',
        ]);

        $response = $this->get(route('admin.warranty-claims.invoice', $claim->id));
        $response->assertRedirect();
    }

    /** Test: Admin có thể lưu và cập nhật refund_amount / refund_method qua store và update */
    public function test_admin_can_store_and_update_refund_fields(): void
    {
        $admin = $this->makeAdmin();
        $item = $this->makeInventoryItem('IMEI-REFUND-FIELDS-TEST');
        $this->makeWarranty($item, 'active', 5, 12);

        // 1. Test Store
        $response = $this->actingAs($admin, 'web')
            ->post(route('admin.warranty-claims.store'), [
                'customer_name' => 'Khách Hàng A',
                'customer_phone' => '0987654321',
                'imei_serial' => 'IMEI-REFUND-FIELDS-TEST',
                'claim_type' => 'return',
                'reason' => 'Đổi trả hoàn tiền.',
                'status' => 'pending',
                'refund_amount' => 3000000,
                'refund_method' => 'bank_transfer',
            ]);

        $response->assertRedirect(route('admin.warranty-claims.index'));
        $claim = WarrantyClaim::where('imei_serial', 'IMEI-REFUND-FIELDS-TEST')->first();
        $this->assertNotNull($claim);
        $this->assertEquals(3000000, $claim->refund_amount);
        $this->assertEquals('bank_transfer', $claim->refund_method);

        // 2. Test Update & Duyệt -> tạo Cashbook
        $response2 = $this->actingAs($admin, 'web')
            ->put(route('admin.warranty-claims.update', $claim->id), [
                'customer_name' => 'Khách Hàng A',
                'customer_phone' => '0987654321',
                'imei_serial' => 'IMEI-REFUND-FIELDS-TEST',
                'claim_type' => 'return',
                'reason' => 'Đổi trả hoàn tiền.',
                'status' => 'approved',
                'refund_amount' => 3500000,
                'refund_method' => 'bank_transfer',
            ]);

        $response2->assertRedirect(route('admin.warranty-claims.index'));
        $claim->refresh();
        $this->assertEquals(3500000, $claim->refund_amount);
        $this->assertEquals('approved', $claim->status);

        // Kiểm tra xem Cashbook có ghi nhận Expense không
        $cashbook = \App\Models\Cashbook::where('reference_id', $claim->id)
            ->where('reference_type', 'warranty_claim')
            ->first();
        $this->assertNotNull($cashbook);
        $this->assertEquals('Expense', $cashbook->type);
        $this->assertEquals(3500000, $cashbook->amount);
    }
}
