<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Ports services.MarketplaceService — public catalogue of purchasable products/plans.
 */
class MarketplaceService
{
    public function getAllMarketplaceProducts(int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->where('is_active', true)
            ->with(['licensePlans' => fn ($q) => $q->where('is_active', true)->orderBy('price')])
            ->withMin(['licensePlans as min_price' => fn ($q) => $q->where('is_active', true)], 'price')
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function getProductDetail(string $productId): Product
    {
        $product = Product::query()
            ->where('id', $productId)
            ->where('is_active', true)
            ->with(['licensePlans' => fn ($q) => $q->where('is_active', true)->with('licenseType')->orderBy('price')])
            ->first();

        if (! $product) {
            throw new NotFoundException("Product not found: {$productId}");
        }

        return $product;
    }
}
