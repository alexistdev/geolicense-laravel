<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Provide admin credentials via env so the seeder never prompts interactively.
        putenv('ADMIN_EMAIL=seed-admin@example.com');
        putenv('ADMIN_PASSWORD=seed-password-123');
        putenv('ADMIN_NAME=Seed Admin');
    }

    protected function tearDown(): void
    {
        putenv('ADMIN_EMAIL');
        putenv('ADMIN_PASSWORD');
        putenv('ADMIN_NAME');
        parent::tearDown();
    }

    public function test_it_seeds_the_canonical_products_with_expected_skus(): void
    {
        $this->seed(ProductionSeeder::class);

        $this->assertDatabaseHas('glo_products', ['sku' => 'GEOCAT', 'name' => 'GeoCAT License', 'is_active' => true]);
        $this->assertDatabaseHas('glo_products', ['sku' => 'GEOBILL', 'name' => 'GeoBill License', 'is_active' => true]);
    }

    public function test_reseeding_is_idempotent(): void
    {
        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class);

        $this->assertSame(1, Product::where('sku', 'GEOCAT')->count());
        $this->assertSame(1, Product::where('sku', 'GEOBILL')->count());
    }

    public function test_sku_column_is_unique(): void
    {
        Product::create(['name' => 'First', 'version' => '1.0', 'sku' => 'DUP', 'is_active' => true]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Product::create(['name' => 'Second', 'version' => '1.0', 'sku' => 'DUP', 'is_active' => true]);
    }
}
