<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicensePlan;
use App\Models\LicenseType;
use App\Models\Product;
use App\Models\SystemLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LicensePlanController extends Controller
{
    /** Billing cycles supported by the marketplace (matches DatabaseSeeder). */
    private const BILLING_CYCLES = ['MONTHLY', 'YEARLY'];

    public function index(Request $request)
    {
        $keyword = $request->query('filter');

        $licensePlans = LicensePlan::query()
            ->with(['product', 'licenseType'])
            ->when($keyword, fn ($q) => $q->where('name', 'like', '%'.$keyword.'%'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $products = Product::query()->orderBy('name')->get(['id', 'name']);
        $licenseTypes = LicenseType::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.license-plans.index', compact('licensePlans', 'products', 'licenseTypes', 'keyword'));
    }

    public function store(Request $request): RedirectResponse
    {
        $licensePlan = LicensePlan::create($this->validated($request));

        SystemLog::record(
            "License plan '{$licensePlan->name}' was created.",
            'INFO',
            ['action' => 'License Plan Created', 'context' => ['license_plan_id' => $licensePlan->id]],
        );

        return back()->with('success', 'License plan added successfully.');
    }

    public function update(Request $request, LicensePlan $licensePlan): RedirectResponse
    {
        $licensePlan->update($this->validated($request));

        SystemLog::record(
            "License plan '{$licensePlan->name}' was updated.",
            'INFO',
            ['action' => 'License Plan Updated', 'context' => ['license_plan_id' => $licensePlan->id]],
        );

        return back()->with('success', 'License plan updated successfully.');
    }

    public function destroy(LicensePlan $licensePlan): RedirectResponse
    {
        SystemLog::record(
            "License plan '{$licensePlan->name}' was deleted.",
            'WARNING',
            ['action' => 'License Plan Deleted', 'context' => ['license_plan_id' => $licensePlan->id]],
        );

        $licensePlan->delete();

        return back()->with('success', 'License plan deleted successfully.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'product_id' => ['required', 'string', Rule::exists('glo_products', 'id')],
            'license_type_id' => ['required', 'string', Rule::exists('glo_license_types', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'billing_cycle' => ['required', Rule::in(self::BILLING_CYCLES)],
            'duration_days' => ['required', 'integer', 'min:1'],
            'max_seats' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['currency'] = strtoupper($data['currency']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
