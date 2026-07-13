<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SystemLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->query('filter');

        $products = Product::query()
            ->when($keyword, fn ($q) => $q
                ->where('name', 'like', '%'.$keyword.'%')
                ->orWhere('sku', 'like', '%'.$keyword.'%'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'keyword'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $product = Product::create($data);

        SystemLog::record(
            "Product '{$product->name}' (SKU: {$product->sku}) was created.",
            'INFO',
            ['action' => 'Product Created', 'context' => ['product_id' => $product->id]],
        );

        return back()->with('success', 'Product added successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);
        $product->update($data);

        SystemLog::record(
            "Product '{$product->name}' (SKU: {$product->sku}) was updated.",
            'INFO',
            ['action' => 'Product Updated', 'context' => ['product_id' => $product->id]],
        );

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        SystemLog::record(
            "Product '{$product->name}' (SKU: {$product->sku}) was deleted.",
            'WARNING',
            ['action' => 'Product Deleted', 'context' => ['product_id' => $product->id]],
        );

        $product->delete();

        return back()->with('success', 'Product deleted successfully.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'version' => ['required', 'string', 'max:10'],
            'sku' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
