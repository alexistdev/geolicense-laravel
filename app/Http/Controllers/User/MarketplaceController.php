<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\LicenseService;
use App\Services\MarketplaceService;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function __construct(
        private readonly MarketplaceService $marketplaceService,
        private readonly LicenseService $licenseService,
    ) {}

    public function index()
    {
        $products = $this->marketplaceService->getAllMarketplaceProducts()->withQueryString();

        return view('user.marketplace.index', compact('products'));
    }

    public function show(Request $request, string $product)
    {
        $product = $this->marketplaceService->getProductDetail($product);

        // Free plans are one-per-product; used to disable the button once claimed.
        $ownsFreeLicense = $this->licenseService
            ->userHasFreeLicenseForProduct($request->user()->id, $product->id);

        return view('user.marketplace.show', compact('product', 'ownsFreeLicense'));
    }
}
