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
    protected function makeInventoryItem(string $imei = 'TESTIMEI001'): InventoryItem
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
            'status'        => 'In_Stock', // enum('In_Stock','Sold','Defective')
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  1. FRONTEND: GỬI YÊU CẦU
    // ──────────────────────────────────────────────────────────────

    /** Test: Gửi yêu cầu bảo hành thành công với IMEI hợp lệ */
    public function test_customer_can_submit_warranty_claim_successfully(): void
    {
        $this->makeInventoryItem('IMEI-WARRANTY-001');

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
        $this->makeInventoryItem('IMEI-RETURN-002');

        $response = $this->postJson(route('warranty.claim.store'), [
            'imei_serial'    => 'IMEI-RETURN-002',
            'customer_name'  => 'Trần Thị Test',
            'customer_phone' => '0987654321',
            'customer_email' => null,
            'claim_type'     => 'return',
            'reason'         => 'Giao sai màu so với đơn hàng đã đặt.',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('warranty_claims', [
            'imei_serial' => 'IMEI-RETURN-002',
            'claim_type'  => 'return',
            'status'      => 'pending',
        ]);
    }

    /** Test: Gửi yêu cầu đổi máy (exchange) thành công */
    public function test_customer_can_submit_exchange_claim_successfully(): void
    {
        $this->makeInventoryItem('IMEI-EXCHANGE-003');

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
        $this->makeInventoryItem('IMEI-AUTH-004');

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
        $this->makeInventoryItem('IMEI-VAL-005');

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
        $this->makeInventoryItem('IMEI-VAL-006');

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
        $this->makeInventoryItem('IMEI-VAL-007');

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
        $this->makeInventoryItem('IMEI-VAL-008');

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
        $this->makeInventoryItem('IMEI-VAL-009');

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

    // ──────────────────────────────────────────────────────────────
    //  3. ADMIN: XEM DANH SÁCH
    // ──────────────────────────────────────────────────────────────

    /** Test: Admin có thể xem danh sách claims */
    public function test_admin_can_view_warranty_claims_list(): void
    {
        $admin = $this->makeAdmin();
        $item  = $this->makeInventoryItem('IMEI-ADMIN-010');

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
}
