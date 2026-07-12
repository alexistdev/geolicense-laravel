<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
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
}
