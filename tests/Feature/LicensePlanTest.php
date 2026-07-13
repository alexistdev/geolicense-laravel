<?php

namespace Tests\Feature;

use App\Models\LicensePlan;
use App\Models\LicenseType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicensePlanTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        return Product::create([
            'name' => 'GeoCAT', 'version' => '1.0', 'sku' => 'GEOCAT', 'is_active' => true,
        ]);
    }

    private function licenseType(): LicenseType
    {
        return LicenseType::create(['name' => 'Standard', 'is_trial' => false]);
    }

    /** @return array<string, mixed> */
    private function validPayload(Product $product, LicenseType $type, array $overrides = []): array
    {
        return array_merge([
            'product_id' => $product->id,
            'license_type_id' => $type->id,
            'name' => 'Monthly Premium',
            'billing_cycle' => 'MONTHLY',
            'duration_days' => 30,
            'max_seats' => 5,
            'price' => 100000,
            'currency' => 'IDR',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_admin_can_view_the_license_plans_page(): void
    {
        $admin = User::factory()->admin()->create();
        $product = $this->product();
        $type = $this->licenseType();
        LicensePlan::create($this->validPayload($product, $type, ['name' => 'Yearly Premium', 'is_active' => true]));

        $this->actingAs($admin)
            ->get('/admin/license_plans')
            ->assertOk()
            ->assertSee('License Plans')
            ->assertSee('Yearly Premium');
    }

    public function test_creating_a_license_plan_persists_and_writes_a_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $product = $this->product();
        $type = $this->licenseType();

        $this->actingAs($admin)
            ->post('/admin/license_plans', $this->validPayload($product, $type))
            ->assertRedirect();

        $this->assertDatabaseHas('glo_license_plans', [
            'name' => 'Monthly Premium',
            'product_id' => $product->id,
            'license_type_id' => $type->id,
            'billing_cycle' => 'MONTHLY',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'License Plan Created',
            'level' => 'INFO',
            'causer' => $admin->email,
            'description' => "License plan 'Monthly Premium' was created.",
        ]);
    }

    public function test_updating_a_license_plan_writes_a_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = LicensePlan::create($this->validPayload($this->product(), $this->licenseType(), ['name' => 'Old Plan']));

        $this->actingAs($admin)->put("/admin/license_plans/{$plan->id}", $this->validPayload(
            Product::first(), LicenseType::first(), ['name' => 'New Plan', 'price' => 250000],
        ));

        $this->assertDatabaseHas('glo_license_plans', ['id' => $plan->id, 'name' => 'New Plan', 'price' => 250000]);
        $this->assertDatabaseHas('glo_logs', [
            'action' => 'License Plan Updated',
            'level' => 'INFO',
            'description' => "License plan 'New Plan' was updated.",
        ]);
    }

    public function test_deleting_a_license_plan_writes_a_warning_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = LicensePlan::create($this->validPayload($this->product(), $this->licenseType(), ['name' => 'Doomed Plan']));

        $this->actingAs($admin)->delete("/admin/license_plans/{$plan->id}");

        $this->assertSoftDeleted('glo_license_plans', ['id' => $plan->id]);
        $this->assertDatabaseHas('glo_logs', [
            'action' => 'License Plan Deleted',
            'level' => 'WARNING',
            'description' => "License plan 'Doomed Plan' was deleted.",
        ]);
    }

    public function test_creating_a_license_plan_requires_valid_product_and_type(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/license_plans', $this->validPayload($this->product(), $this->licenseType(), [
                'product_id' => 'non-existent-id',
                'billing_cycle' => 'WEEKLY',
            ]))
            ->assertSessionHasErrors(['product_id', 'billing_cycle']);
    }
}
