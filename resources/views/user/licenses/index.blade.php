<x-app-layout title="My Licenses — GeoLicense" header="My Account">
    <div class="p-8 space-y-6">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">My Licenses</h1>
            <p class="text-on-surface-variant text-sm mt-1">License keys issued to your account.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse ($licenses as $license)
                <a href="{{ route('user.license.show', $license->id) }}" class="bg-surface-container rounded-2xl p-6 hover:bg-surface-container-high transition-all border border-transparent hover:border-primary/20">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[0.625rem] uppercase tracking-widest text-on-surface-variant">{{ $license->licensePlan?->product?->name ?? $license->product?->name }}</p>
                            <p class="font-mono text-primary text-sm mt-1 break-all">{{ $license->license_key }}</p>
                        </div>
                        <x-status-badge :status="$license->status" />
                    </div>
                    <div class="grid grid-cols-3 gap-3 mt-5 pt-4 border-t border-white/5 text-center">
                        <div>
                            <p class="text-[0.625rem] uppercase text-on-surface-variant">Plan</p>
                            <p class="text-on-surface text-xs font-semibold mt-1">{{ $license->licensePlan?->name }}</p>
                        </div>
                        <div>
                            <p class="text-[0.625rem] uppercase text-on-surface-variant">Seats</p>
                            <p class="text-on-surface text-xs font-semibold mt-1">{{ $license->used_seats }}/{{ $license->max_seats }}</p>
                        </div>
                        <div>
                            <p class="text-[0.625rem] uppercase text-on-surface-variant">Expires</p>
                            <p class="text-on-surface text-xs font-semibold mt-1">{{ $license->expires_at?->format('d M Y') }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl">key_off</span>
                    <p class="mt-2">No licenses yet. Purchase one from the <a href="{{ route('user.marketplace.index') }}" class="text-primary">marketplace</a>.</p>
                </div>
            @endforelse
        </div>
        @include('partials.pagination', ['paginator' => $licenses])
    </div>
</x-app-layout>
