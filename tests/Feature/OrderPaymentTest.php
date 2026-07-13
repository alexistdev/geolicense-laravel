<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\License;
use App\Models\LicensePlan;
use App\Models\LicenseType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function makePlan(): LicensePlan
    {
        $product = Product::create(['name' => 'App', 'version' => '1.0', 'sku' => 'SKU-X', 'is_active' => true]);
        $type = LicenseType::create(['name' => 'Premium', 'is_trial' => false]);

        return LicensePlan::create([
            'product_id' => $product->id, 'license_type_id' => $type->id, 'name' => 'Monthly',
            'billing_cycle' => 'MONTHLY', 'duration_days' => 30, 'max_seats' => 5, 'price' => 100000, 'currency' => 'IDR', 'is_active' => true,
        ]);
    }

    private function makeFreePlan(?Product $product = null, string $name = 'Free'): LicensePlan
    {
        $product ??= Product::create(['name' => 'App '.uniqid(), 'version' => '1.0', 'sku' => 'SKU-'.uniqid(), 'is_active' => true]);
        $type = LicenseType::create(['name' => 'Free', 'is_trial' => true]);

        return LicensePlan::create([
            'product_id' => $product->id, 'license_type_id' => $type->id, 'name' => $name,
            'billing_cycle' => 'MONTHLY', 'duration_days' => 30, 'max_seats' => 1, 'price' => 0, 'currency' => 'IDR', 'is_active' => true,
        ]);
    }

    public function test_order_creates_an_unpaid_invoice(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan();

        $this->actingAs($user)
            ->post('/user/orders', ['license_plan_id' => $plan->id])
            ->assertRedirect();

        $this->assertDatabaseHas('glo_orders', ['user_id' => $user->id, 'status' => OrderStatus::PENDING->value]);
        $this->assertDatabaseHas('glo_invoices', ['status' => InvoiceStatus::UNPAID->value]);
    }

    public function test_full_payment_lifecycle_issues_a_license(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $plan = $this->makePlan();

        // 1. User orders → invoice UNPAID
        $this->actingAs($user)->post('/user/orders', ['license_plan_id' => $plan->id]);
        $invoice = Invoice::firstOrFail();

        // 2. User submits payment → AWAITING_VERIFICATION
        $this->actingAs($user)->post("/user/invoice/{$invoice->id}/payment", [
            'provider' => 'Bank Transfer', 'provider_reference' => 'TRX-123',
        ])->assertRedirect();

        $this->assertEquals(InvoiceStatus::AWAITING_VERIFICATION, $invoice->fresh()->status);
        $this->assertDatabaseHas('glo_payments', ['provider_reference' => 'TRX-123', 'status' => PaymentStatus::PENDING->value]);

        // 3. Admin validates → PAID + license issued
        $this->actingAs($admin)->patch("/admin/invoices/{$invoice->id}/validate")->assertRedirect();

        $this->assertEquals(InvoiceStatus::PAID, $invoice->fresh()->status);
        $this->assertEquals(OrderStatus::COMPLETED, $invoice->order->fresh()->status);
        $this->assertDatabaseHas('glo_licenses', ['user_id' => $user->id]);
    }

    public function test_pending_invoice_blocks_a_second_order(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlan();

        $this->actingAs($user)->post('/user/orders', ['license_plan_id' => $plan->id]);
        $this->actingAs($user)
            ->from('/user/marketplace')
            ->post('/user/orders', ['license_plan_id' => $plan->id])
            ->assertRedirect('/user/marketplace');

        $this->assertEquals(1, Invoice::count());
    }

    public function test_free_plan_activates_license_instantly_without_billing(): void
    {
        $user = User::factory()->create();
        $plan = $this->makeFreePlan();

        $this->actingAs($user)
            ->post('/user/orders', ['license_plan_id' => $plan->id])
            ->assertRedirect('/user/license');

        // Order completed, invoice paid with no unique code billed.
        $this->assertDatabaseHas('glo_orders', ['user_id' => $user->id, 'status' => OrderStatus::COMPLETED->value]);
        $this->assertDatabaseHas('glo_invoices', ['status' => InvoiceStatus::PAID->value, 'unique_code' => 0, 'total_amount' => '0.0000']);
        $this->assertDatabaseHas('glo_payments', ['provider' => 'FREE', 'status' => PaymentStatus::VERIFIED->value]);

        // License is issued and active immediately.
        $this->assertDatabaseHas('glo_licenses', [
            'user_id' => $user->id, 'product_id' => $plan->product_id, 'status' => 'ACTIVE',
        ]);
    }

    public function test_free_plan_is_limited_to_one_per_product(): void
    {
        $user = User::factory()->create();
        $plan = $this->makeFreePlan();

        $this->actingAs($user)->post('/user/orders', ['license_plan_id' => $plan->id]);

        $this->actingAs($user)
            ->from('/user/marketplace')
            ->post('/user/orders', ['license_plan_id' => $plan->id])
            ->assertRedirect('/user/marketplace')
            ->assertSessionHas('error');

        $this->assertEquals(1, License::where('user_id', $user->id)->count());
    }

    public function test_free_license_limit_is_scoped_per_product(): void
    {
        $user = User::factory()->create();
        $freeA = $this->makeFreePlan();
        $freeB = $this->makeFreePlan();

        $this->actingAs($user)->post('/user/orders', ['license_plan_id' => $freeA->id])->assertRedirect('/user/license');
        // A free license for a different product is still allowed.
        $this->actingAs($user)->post('/user/orders', ['license_plan_id' => $freeB->id])->assertRedirect('/user/license');

        $this->assertEquals(2, License::where('user_id', $user->id)->count());
    }
}
