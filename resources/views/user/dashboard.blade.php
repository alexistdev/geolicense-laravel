<x-app-layout title="Dashboard — GeoLicense" header="My Account">
    <div class="p-8 space-y-8">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight">Welcome back, {{ explode(' ', auth()->user()->full_name)[0] }}</h1>
            <p class="text-on-surface-variant text-sm mt-1">Here's an overview of your licenses and billing.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @php
                $tiles = [
                    ['Active Licenses', number_format($stats['active_licenses']), 'vpn_key', 'text-primary'],
                    ['Total Licenses', number_format($stats['total_licenses']), 'inventory_2', 'text-on-surface'],
                    ['Pending Invoices', number_format($stats['pending_invoices']), 'pending', 'text-tertiary'],
                    ['Paid Invoices', number_format($stats['paid_invoices']), 'receipt_long', 'text-primary'],
                ];
            @endphp
            @foreach ($tiles as [$label, $value, $icon, $color])
                <div class="bg-surface-container p-6 rounded-xl flex flex-col justify-between hover:bg-surface-container-high transition-all">
                    <div>
                        <span class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-[0.1em]">{{ $label }}</span>
                        <h2 class="text-4xl font-extrabold mt-2 {{ $color }} tracking-tight">{{ $value }}</h2>
                    </div>
                    <div class="mt-4 flex items-center gap-2 {{ $color }}">
                        <span class="material-symbols-outlined text-sm">{{ $icon }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-surface-container rounded-2xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-white">My Recent Licenses</h3>
                    <a href="{{ route('user.license.index') }}" class="text-primary text-sm hover:text-primary-container">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-on-surface-variant text-[0.6875rem] uppercase tracking-widest border-b border-white/5">
                                <th class="py-3">License Key</th>
                                <th class="py-3">Product</th>
                                <th class="py-3">Status</th>
                                <th class="py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse ($recentLicenses as $license)
                                <tr class="hover:bg-white/5">
                                    <td class="py-3 font-mono text-primary text-xs">{{ $license->license_key }}</td>
                                    <td class="py-3 text-on-surface">{{ $license->product?->name ?? $license->licensePlan?->product?->name }}</td>
                                    <td class="py-3"><x-status-badge :status="$license->status" /></td>
                                    <td class="py-3 text-right"><a href="{{ route('user.license.show', $license->id) }}" class="text-primary text-sm">Details</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-on-surface-variant">You have no licenses yet. Visit the <a href="{{ route('user.marketplace.index') }}" class="text-primary">marketplace</a>.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-gradient-to-br from-primary-container/20 to-surface-container rounded-2xl p-6 flex flex-col justify-between">
                <div>
                    <span class="material-symbols-outlined text-primary text-3xl">storefront</span>
                    <h3 class="text-lg font-bold text-white mt-3">Need more licenses?</h3>
                    <p class="text-on-surface-variant text-sm mt-1">Browse products and pick a plan that fits your team.</p>
                </div>
                <a href="{{ route('user.marketplace.index') }}" class="mt-6 inline-flex items-center justify-center gap-2 py-3 px-4 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold rounded-lg text-sm">
                    Open Marketplace <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
