<x-app-layout title="{{ $product->name }} — GeoLicense" header="Marketplace">
    <div class="p-8 space-y-6 max-w-5xl">
        <a href="{{ route('user.marketplace.index') }}" class="inline-flex items-center gap-1 text-on-surface-variant hover:text-primary text-sm">
            <span class="material-symbols-outlined text-base">arrow_back</span> Back to marketplace
        </a>

        <div class="bg-surface-container rounded-2xl p-8">
            <div class="flex items-start gap-5">
                <div class="w-16 h-16 rounded-2xl bg-primary-container/20 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-primary text-3xl">deployed_code</span>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-black text-white">{{ $product->name }}</h1>
                        <span class="px-2 py-0.5 rounded-full bg-surface-container-highest text-on-surface-variant text-xs font-bold">v{{ $product->version }}</span>
                    </div>
                    <p class="text-on-surface-variant mt-2">{{ $product->description }}</p>
                    <p class="text-xs text-on-surface-variant/60 mt-2 font-mono">SKU: {{ $product->sku }}</p>
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-bold text-white mb-4">Choose a plan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse ($product->licensePlans as $plan)
                    @php $isFree = (float) $plan->price == 0.0; @endphp
                    <div class="bg-surface-container rounded-2xl p-6 border border-white/5 flex flex-col">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white">{{ $plan->name }}</h3>
                            <span class="px-2.5 py-1 rounded-full bg-secondary-container/40 text-secondary text-[0.625rem] font-bold uppercase tracking-wider">{{ $plan->billing_cycle }}</span>
                        </div>
                        <p class="text-3xl font-black text-primary mt-4">{{ $isFree ? 'Free' : money($plan->price, $plan->currency) }}</p>
                        <ul class="mt-5 space-y-2 text-sm text-on-surface-variant flex-1">
                            <li class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-base">check</span> {{ $plan->duration_days }} days validity</li>
                            <li class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-base">check</span> Up to {{ $plan->max_seats }} machines</li>
                            <li class="flex items-center gap-2"><span class="material-symbols-outlined text-primary text-base">check</span> {{ $plan->licenseType?->name ?? 'License' }}</li>
                        </ul>
                        @if ($isFree && $ownsFreeLicense)
                            <button type="button" disabled class="mt-6 w-full py-3 rounded-lg bg-surface-container-highest text-on-surface-variant/60 font-bold text-sm flex items-center justify-center gap-2 cursor-not-allowed">
                                <span class="material-symbols-outlined text-lg">check_circle</span> Free license claimed
                            </button>
                            <p class="mt-2 text-xs text-on-surface-variant/60 text-center">Choose a Premium plan for another license.</p>
                        @else
                            <form method="POST" action="{{ route('user.orders.store') }}" class="mt-6">
                                @csrf
                                <input type="hidden" name="license_plan_id" value="{{ $plan->id }}">
                                <button type="submit" class="w-full py-3 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-sm flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-lg">{{ $isFree ? 'bolt' : 'shopping_cart' }}</span> {{ $isFree ? 'Activate free license' : 'Order this plan' }}
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-on-surface-variant">No plans available for this product yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
