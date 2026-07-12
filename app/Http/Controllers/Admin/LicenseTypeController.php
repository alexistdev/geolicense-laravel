<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LicenseTypeController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->query('filter');

        $licenseTypes = LicenseType::query()
            ->when($keyword, fn ($q) => $q->where('name', 'like', '%'.$keyword.'%'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.license-types.index', compact('licenseTypes', 'keyword'));
    }

    public function store(Request $request): RedirectResponse
    {
        LicenseType::create($this->validated($request));

        return back()->with('success', 'License type added successfully.');
    }

    public function update(Request $request, LicenseType $licenseType): RedirectResponse
    {
        $licenseType->update($this->validated($request));

        return back()->with('success', 'License type updated successfully.');
    }

    public function destroy(LicenseType $licenseType): RedirectResponse
    {
        $licenseType->delete();

        return back()->with('success', 'License type deleted successfully.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_trial' => ['nullable', 'boolean'],
        ]);
        $data['is_trial'] = $request->boolean('is_trial');

        return $data;
    }
}
