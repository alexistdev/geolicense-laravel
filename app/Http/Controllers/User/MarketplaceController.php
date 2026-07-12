<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\MarketplaceService;

class MarketplaceController extends Controller
{
    public function __construct(private readonly MarketplaceService $marketplaceService) {}

    public function index()
    {
        $products = $this->marketplaceService->getAllMarketplaceProducts()->withQueryString();

        return view('user.marketplace.index', compact('products'));
    }

    public function show(string $product)
    {
        $product = $this->marketplaceService->getProductDetail($product);

        return view('user.marketplace.show', compact('product'));
    }
}
