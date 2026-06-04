<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Services\AuditHasher;
use App\Services\AuditMasker;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->seed(RoleSeeder::class);

        // Create admin user
        $this->admin = User::create([
            'role_id' => 1, // Admin
            'full_name' => 'System Auditor',
            'email' => 'auditor@dienmaypro.vn',
            'password_hash' => bcrypt('password123'),
            'status' => 'Active',
        ]);

        // Create category
        $this->category = Category::create([
            'name' => 'Tivi Cục Gạch',
            'slug' => 'tivi-cuc-gach',
        ]);
    }

    /**
     * Test AuditMasker masks sensitive data properly.
     */
    public function test_audit_masker_masks_sensitive_fields(): void
    {
        $rawData = [
            'name' => 'Nguyen Van A',
            'password' => 'superSecret123',
            'password_hash' => '$2y$10$abcdefgh...',
            'address' => [
                'city' => 'Hanoi',
                'otp_code' => '123456',
            ]
        ];

        $masked = AuditMasker::mask($rawData);

        $this->assertEquals('Nguyen Van A', $masked['name']);
        $this->assertEquals('******** [MASKED]', $masked['password']);
        $this->assertEquals('******** [MASKED]', $masked['password_hash']);
        $this->assertEquals('Hanoi', $masked['address']['city']);
        $this->assertEquals('******** [MASKED]', $masked['address']['otp_code']);
    }

    /**
     * Test AuditHasher produces deterministic sorted JSON.
     */
    public function test_audit_hasher_canonicalizes_json(): void
    {
        $data1 = ['z' => 1, 'a' => 2, 'm' => 3];
        $data2 = ['a' => 2, 'm' => 3, 'z' => 1];

        $json1 = AuditHasher::canonicalizeJson($data1);
        $json2 = AuditHasher::canonicalizeJson($data2);

        $this->assertEquals($json1, $json2);
        $this->assertEquals('{"a":2,"m":3,"z":1}', $json1);
    }

    /**
     * Test that Eloquent hooks automatically record audit logs for product updates.
     */
    public function test_eloquent_hooks_automatically_log_activity(): void
    {
        // 1. Act as the Admin
        $this->actingAs($this->admin);

        // 2. Create a Product
        $product = Product::create([
            'category_id' => $this->category->category_id,
            'name' => 'Tivi Sieu Mong',
            'base_price' => 5000000,
            'seo_description' => 'Tivi Sony cu',
            'safe_stock' => 10,
        ]);

        // Assert that a log was created for 'created' event
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'created',
            'subject_type' => Product::class,
            'subject_id' => $product->product_id,
            'causer_id' => $this->admin->user_id,
        ]);

        // 3. Update the Product
        $product->update([
            'base_price' => 5500000,
            'seo_description' => 'Moi 99%',
        ]);

        // Assert that a log was created for 'updated' event
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'updated',
            'subject_type' => Product::class,
            'subject_id' => $product->product_id,
        ]);

        // Verify the values recorded in log
        $latestLog = ActivityLog::where('event', 'updated')
            ->where('subject_id', $product->product_id)
            ->first();

        $this->assertNotNull($latestLog);
        $this->assertEquals(5000000, $latestLog->old_values['base_price']);
        $this->assertEquals(5500000, $latestLog->new_values['base_price']);
    }

    /**
     * Test Hash chaining logic and visual integrity verification.
     */
    public function test_hash_chaining_integrity_verification_and_tamper_detection(): void
    {
        $this->actingAs($this->admin);

        // 1. Perform multiple actions to generate log entries
        $product = Product::create([
            'category_id' => $this->category->category_id,
            'name' => 'Tivi Sony 4K',
            'base_price' => 10000000,
            'safe_stock' => 5,
        ]);

        $product->update([
            'base_price' => 12000000,
        ]);

        $product->update([
            'name' => 'Tivi Sony 4K Premium',
        ]);

        // Retrieve logs and assert they have cryptographic hash chain values
        $logs = ActivityLog::orderBy('log_id', 'asc')->get();
        $this->assertCount(3, $logs);

        // The first hash is chaining with 64 zeros
        // Verify chain logic
        $computedHash = null;
        foreach ($logs as $log) {
            $payload = [
                'event' => $log->event,
                'causer_type' => $log->causer_type,
                'causer_id' => $log->causer_id,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'old_values' => $log->getRawOriginal('old_values'),
                'new_values' => $log->getRawOriginal('new_values'),
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->toDateTimeString(),
            ];
            $expected = AuditHasher::generateHashChain($payload, $computedHash);
            $this->assertEquals($expected, $log->hash_chain);
            $computedHash = $log->hash_chain;
        }

        // 2. Call Verify endpoint - should succeed
        $response = $this->postJson(route('admin.activity-logs.verify'));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 3. Simulate TAMPERING: Modify a field in the database manually
        // We will change the name of the product inside new_values of the second log entry
        $tamperedLogId = $logs[1]->log_id;
        DB::table('activity_logs')
            ->where('log_id', $tamperedLogId)
            ->update([
                'new_values' => json_encode(['base_price' => 99000000]) // altered price!
            ]);

        // 4. Call Verify endpoint - should detect breach!
        $response = $this->postJson(route('admin.activity-logs.verify'));
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'log_id' => $tamperedLogId
        ]);
        $this->assertStringContainsString('Sai lệch mã hash tại log ID', $response->json('details'));
    }
}
