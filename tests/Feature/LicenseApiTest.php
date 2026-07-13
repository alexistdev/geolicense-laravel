<?php

namespace Tests\Feature;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Models\LicensePlan;
use App\Models\LicenseType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeLicense(array $overrides = [], string $sku = 'SKU-X'): License
    {
        $user = User::factory()->create();
        $product = Product::create(['name' => 'App', 'version' => '1.0', 'sku' => $sku, 'is_active' => true]);
        $type = LicenseType::create(['name' => 'Premium', 'is_trial' => false]);
        $plan = LicensePlan::create([
            'product_id' => $product->id, 'license_type_id' => $type->id, 'name' => 'Monthly',
            'billing_cycle' => 'MONTHLY', 'duration_days' => 30, 'max_seats' => 2, 'price' => 1000, 'currency' => 'IDR', 'is_active' => true,
        ]);
        $order = Order::create(['user_id' => $user->id, 'order_number' => 'ORD-T', 'currency' => 'IDR']);
        $item = OrderItem::create(['order_id' => $order->id, 'license_plan_id' => $plan->id, 'quantity' => 1, 'unit_price' => 1000, 'total_price' => 1000]);

        return License::create(array_merge([
            'user_id' => $user->id, 'product_id' => $product->id, 'license_plan_id' => $plan->id, 'order_item_id' => $item->id,
            'license_key' => LicenseService::generateLicenseKey(), 'max_seats' => 2, 'used_seats' => 0,
            'issued_at' => now(), 'expires_at' => now()->addDays(30), 'status' => LicenseStatus::ACTIVE,
        ], $overrides));
    }

    public function test_activate_issues_token_and_records_activation(): void
    {
        $license = $this->makeLicense();

        $response = $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => $license->license_key, 'machineId' => 'MACHINE-1', 'productSku' => 'SKU-X', 'osInfo' => 'macOS',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('payload.valid', true)
            ->assertJsonPath('payload.productSku', 'SKU-X')
            ->assertJsonPath('payload.usedSeats', 1);

        $this->assertNotEmpty($response->json('payload.token'));
        $this->assertDatabaseHas('glo_license_activations', ['machine_id' => 'MACHINE-1']);
        $this->assertEquals(1, $license->fresh()->used_seats);
    }

    public function test_verify_validates_an_activated_token(): void
    {
        $license = $this->makeLicense();

        $token = $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => $license->license_key, 'machineId' => 'MACHINE-1', 'productSku' => 'SKU-X',
        ])->json('payload.token');

        $this->postJson('/api/v1/licenses/verify', ['token' => $token, 'machineId' => 'MACHINE-1', 'productSku' => 'SKU-X'])
            ->assertOk()
            ->assertJsonPath('payload.valid', true)
            ->assertJsonPath('payload.status', 'ACTIVE');
    }

    public function test_seat_limit_returns_429(): void
    {
        $license = $this->makeLicense(['max_seats' => 1, 'used_seats' => 1]);

        $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => $license->license_key, 'machineId' => 'NEW-MACHINE', 'productSku' => 'SKU-X',
        ])->assertStatus(429)->assertJsonPath('status', false);
    }

    public function test_expired_license_returns_402(): void
    {
        $license = $this->makeLicense(['expires_at' => now()->subDay()]);

        $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => $license->license_key, 'machineId' => 'MACHINE-1', 'productSku' => 'SKU-X',
        ])->assertStatus(402)->assertJsonPath('status', false);
    }

    public function test_verify_rejects_machine_mismatch_with_403(): void
    {
        $license = $this->makeLicense();
        $token = $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => $license->license_key, 'machineId' => 'MACHINE-1', 'productSku' => 'SKU-X',
        ])->json('payload.token');

        $this->postJson('/api/v1/licenses/verify', ['token' => $token, 'machineId' => 'OTHER-MACHINE', 'productSku' => 'SKU-X'])
            ->assertStatus(403)->assertJsonPath('status', false);
    }

    public function test_activate_rejects_wrong_product_with_403(): void
    {
        // License issued for GeoCAT (SKU-X); client claims it is GeoBill (SKU-Y).
        $license = $this->makeLicense();

        $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => $license->license_key, 'machineId' => 'MACHINE-1', 'productSku' => 'SKU-Y',
        ])->assertStatus(403)->assertJsonPath('status', false);

        // Seat must NOT be consumed on a rejected activation.
        $this->assertEquals(0, $license->fresh()->used_seats);
        $this->assertDatabaseMissing('glo_license_activations', ['machine_id' => 'MACHINE-1']);
    }

    public function test_activate_requires_product_sku(): void
    {
        $license = $this->makeLicense();

        $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => $license->license_key, 'machineId' => 'MACHINE-1',
        ])->assertStatus(422)->assertJsonValidationErrors('productSku');
    }

    public function test_verify_rejects_token_used_for_another_product(): void
    {
        // A valid GeoCAT token replayed by the GeoBill client must be refused.
        $license = $this->makeLicense();
        $token = $this->postJson('/api/v1/licenses/activate', [
            'licenseKey' => $license->license_key, 'machineId' => 'MACHINE-1', 'productSku' => 'SKU-X',
        ])->json('payload.token');

        $this->postJson('/api/v1/licenses/verify', ['token' => $token, 'machineId' => 'MACHINE-1', 'productSku' => 'SKU-Y'])
            ->assertStatus(403)->assertJsonPath('status', false);
    }
}
