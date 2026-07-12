<x-app-layout title="Admin Dashboard — GeoLicense" header="Admin Console">
    <div class="p-8 space-y-8">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight">Command Center</h1>
            <p class="text-on-surface-variant text-sm mt-1">Real-time overview of licenses, revenue and pending clearances.</p>
        </div>

        {{-- Stat tiles --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            @php
                $tiles = [
                    ['Active Licenses', number_format($stats['active_licenses']), 'verified', 'text-primary'],
                    ['Total Revenue', money($stats['total_revenue']), 'payments', 'text-primary'],
                    ['Pending Clearances', number_format($stats['pending_clearances']), 'priority_high', 'text-tertiary'],
                    ['Registered Users', number_format($stats['total_users']), 'group', 'text-primary'],
                ];
            @endphp
            @foreach ($tiles as [$label, $value, $icon, $color])
                <div class="bg-surface-container p-6 rounded-xl flex flex-col justify-between transition-all hover:bg-surface-container-high">
                    <div>
                        <span class="text-[0.6875rem] font-bold text-on-surface-variant uppercase tracking-[0.1em]">{{ $label }}</span>
                        <h2 class="text-4xl font-extrabold mt-2 {{ $color }} tracking-tight">{{ $value }}</h2>
                    </div>
                    <div class="mt-4 flex items-center gap-2 {{ $color }}">
                        <span class="material-symbols-outlined text-sm">{{ $icon }}</span>
                        <span class="text-xs font-bold uppercase tracking-wider opacity-80">GeoLicense</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Recent licenses --}}
            <div class="lg:col-span-2 bg-surface-container rounded-2xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-white">Recently Issued Licenses</h3>
                    <span class="material-symbols-outlined text-on-surface-variant">vpn_key</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-on-surface-variant text-[0.6875rem] uppercase tracking-widest border-b border-white/5">
                                <th class="py-3">License Key</th>
                                <th class="py-3">Holder</th>
                                <th class="py-3">Plan</th>
                                <th class="py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse ($recentLicenses as $license)
                                <tr class="hover:bg-white/5">
                                    <td class="py-3 font-mono text-primary text-xs">{{ $license->license_key }}</td>
                                    <td class="py-3 text-on-surface">{{ $license->user?->full_name }}</td>
                                    <td class="py-3 text-on-surface-variant">{{ $license->licensePlan?->name }}</td>
                                    <td class="py-3"><x-status-badge :status="$license->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-on-surface-variant">No licenses issued yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pending clearances --}}
            <div class="bg-surface-container rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-6">Awaiting Verification</h3>
                <div class="space-y-4">
                    @forelse ($pendingInvoices as $invoice)
                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="block p-4 rounded-lg bg-surface-container-high hover:bg-surface-container-highest transition-colors">
                            <div class="flex justify-between items-center">
                                <span class="font-mono text-xs text-tertiary">{{ $invoice->invoice_number }}</span>
                                <span class="text-sm font-bold text-white">{{ money($invoice->total_amount, $invoice->currency) }}</span>
                            </div>
                            <p class="text-xs text-on-surface-variant mt-1">{{ $invoice->order?->user?->full_name }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-on-surface-variant">No payments awaiting verification.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
