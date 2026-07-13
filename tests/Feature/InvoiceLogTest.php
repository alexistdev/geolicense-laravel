<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\LicensePlan;
use App\Models\LicenseType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceLogTest extends TestCase
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

    /** Order → submit payment, leaving the invoice AWAITING_VERIFICATION. */
    private function invoiceAwaitingVerification(User $user): Invoice
    {
        $plan = $this->makePlan();
        $this->actingAs($user)->post('/user/orders', ['license_plan_id' => $plan->id]);
        $invoice = Invoice::firstOrFail();
        $this->actingAs($user)->post("/user/invoice/{$invoice->id}/payment", [
            'provider' => 'Bank Transfer', 'provider_reference' => 'TRX-123',
        ]);

        return $invoice->fresh();
    }

    public function test_validating_an_invoice_writes_a_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $invoice = $this->invoiceAwaitingVerification(User::factory()->create());

        $this->actingAs($admin)->patch("/admin/invoices/{$invoice->id}/validate")->assertRedirect();

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'Invoice Validated',
            'level' => 'INFO',
            'causer' => $admin->email,
            'description' => "Invoice {$invoice->invoice_number} payment was validated — license(s) issued.",
        ]);
    }

    public function test_rejecting_an_invoice_writes_a_warning_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $invoice = $this->invoiceAwaitingVerification(User::factory()->create());

        $this->actingAs($admin)->patch("/admin/invoices/{$invoice->id}/reject")->assertRedirect();

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'Invoice Rejected',
            'level' => 'WARNING',
            'description' => "Invoice {$invoice->invoice_number} payment was rejected.",
        ]);
    }

    public function test_voiding_an_invoice_writes_a_warning_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        // A fresh order leaves the invoice UNPAID, which can be voided directly.
        $plan = $this->makePlan();
        $this->actingAs($user)->post('/user/orders', ['license_plan_id' => $plan->id]);
        $invoice = Invoice::firstOrFail();

        $this->actingAs($admin)->patch("/admin/invoices/{$invoice->id}/void")->assertRedirect();

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'Invoice Voided',
            'level' => 'WARNING',
            'description' => "Invoice {$invoice->invoice_number} was voided — order cancelled.",
        ]);
    }
}
