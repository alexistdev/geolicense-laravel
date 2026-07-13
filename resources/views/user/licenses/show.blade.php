<x-app-layout title="License Detail — GeoLicense" header="My Account">
    <div class="p-8 space-y-6 max-w-4xl">
        <a href="{{ route('user.license.index') }}" class="inline-flex items-center gap-1 text-on-surface-variant hover:text-primary text-sm">
            <span class="material-symbols-outlined text-base">arrow_back</span> Back to licenses
        </a>

        <div class="bg-surface-container rounded-2xl p-8">
            <div class="flex flex-col md:flex-row justify-between gap-4 pb-6 border-b border-white/5">
                <div>
                    <p class="text-on-surface-variant text-xs uppercase tracking-widest">{{ $license->licensePlan?->product?->name ?? $license->product?->name }}</p>
                    <h1 class="text-on-surface text-2xl font-semibold mt-2">{{ $license->licensePlan?->name ?? '—' }}</h1>
                </div>
                <x-status-badge :status="$license->status" />
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 py-6">
                <div><p class="text-[0.625rem] uppercase text-on-surface-variant">Plan</p><p class="text-on-surface font-semibold mt-1">{{ $license->licensePlan?->name }}</p></div>
                <div><p class="text-[0.625rem] uppercase text-on-surface-variant">Seats</p><p class="text-on-surface font-semibold mt-1">{{ $license->used_seats }} / {{ $license->max_seats }}</p></div>
                <div><p class="text-[0.625rem] uppercase text-on-surface-variant">Issued</p><p class="text-on-surface font-semibold mt-1">{{ $license->issued_at?->format('d M Y') }}</p></div>
                <div><p class="text-[0.625rem] uppercase text-on-surface-variant">Expires</p><p class="text-on-surface font-semibold mt-1">{{ $license->expires_at?->format('d M Y') }}</p></div>
            </div>

            <div class="py-6 border-t border-white/5">
                <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-4">Machine Activations</p>
                @if ($license->activations->isEmpty())
                    <p class="text-sm text-on-surface-variant">No machines have been activated with this license yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-on-surface-variant text-[0.6875rem] uppercase tracking-widest border-b border-white/5">
                                    <th class="py-2">Machine ID</th>
                                    <th class="py-2">OS</th>
                                    <th class="py-2">Activated</th>
                                    <th class="py-2">Last Verified</th>
                                    <th class="py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach ($license->activations as $activation)
                                    <tr>
                                        <td class="py-2.5 font-mono text-xs text-on-surface">{{ $activation->machine_id }}</td>
                                        <td class="py-2.5 text-on-surface-variant">{{ $activation->os_info ?? '—' }}</td>
                                        <td class="py-2.5 text-on-surface-variant text-xs">{{ $activation->activated_at?->format('d M Y H:i') }}</td>
                                        <td class="py-2.5 text-on-surface-variant text-xs">{{ $activation->last_verified_at?->format('d M Y H:i') ?? '—' }}</td>
                                        <td class="py-2.5"><x-status-badge :status="$activation->is_activated ? 'ACTIVE' : 'SUSPENDED'" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="pt-6 border-t border-white/5" x-data="{ copiedKey: false }">
                <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-3">License Key</p>
                <div class="flex items-center gap-3 rounded-lg bg-surface-container-lowest p-4">
                    <p class="flex-1 font-mono text-sm text-primary break-all select-all">{{ $license->license_key }}</p>
                    <button @click="navigator.clipboard.writeText('{{ $license->license_key }}'); copiedKey = true; setTimeout(() => copiedKey = false, 2000)"
                        class="shrink-0 p-1.5 rounded-lg hover:bg-surface-container-high text-on-surface-variant hover:text-primary transition-colors"
                        title="Copy license key">
                        <span class="material-symbols-outlined text-lg" x-text="copiedKey ? 'check' : 'content_copy'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
