<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Canonical products keyed by SKU.
 *
 * The SKU is the stable product identity that each client build (GeoCAT,
 * GeoBill) embeds and sends on activation; LicenseTokenService rejects any
 * activation whose productSku does not match the license's product. These
 * values MUST match the strings compiled into the client apps exactly
 * (case-sensitive).
 *
 * Kept as its own idempotent seeder (firstOrCreate by sku) — like
 * SystemMenuSeeder — so it can be applied to an already-seeded production DB on
 * every deploy without touching existing rows:
 *
 *     php artisan db:seed --class=ProductSeeder --force
 */
class ProductSeeder extends Seeder
{
    private const SYSTEM = 'System';

    public function run(): void
    {
        $products = [
            [
                'sku' => 'GEOCAT',
                'name' => 'GeoCAT License',
                'version' => '1.0',
                'description' => 'GeoCAT desktop application license.',
            ],
            [
                'sku' => 'GEOBILL',
                'name' => 'GeoBill License',
                'version' => '1.0',
                'description' => 'GeoBill billing system license.',
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'version' => $product['version'],
                    'description' => $product['description'],
                    'is_active' => true,
                    'created_by' => self::SYSTEM,
                    'modified_by' => self::SYSTEM,
                ],
            );
        }
    }
}
