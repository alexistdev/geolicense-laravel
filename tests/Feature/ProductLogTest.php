<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_product_writes_a_log_entry(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Acme App',
            'version' => '1.0',
            'sku' => 'SKU-ACME',
        ]);

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'Product Created',
            'level' => 'INFO',
            'causer' => $admin->email,
            'description' => "Product 'Acme App' (SKU: SKU-ACME) was created.",
        ]);
    }

    public function test_updating_a_product_writes_a_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Old Name', 'version' => '1.0', 'sku' => 'SKU-1']);

        $this->actingAs($admin)->put("/admin/products/{$product->id}", [
            'name' => 'New Name',
            'version' => '2.0',
            'sku' => 'SKU-1',
        ]);

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'Product Updated',
            'level' => 'INFO',
            'description' => "Product 'New Name' (SKU: SKU-1) was updated.",
        ]);
    }

    public function test_deleting_a_product_writes_a_warning_log_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Doomed', 'version' => '1.0', 'sku' => 'SKU-DEL']);

        $this->actingAs($admin)->delete("/admin/products/{$product->id}");

        $this->assertDatabaseHas('glo_logs', [
            'action' => 'Product Deleted',
            'level' => 'WARNING',
            'description' => "Product 'Doomed' (SKU: SKU-DEL) was deleted.",
        ]);
    }
}
