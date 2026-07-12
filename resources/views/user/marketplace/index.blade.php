<x-app-layout title="Marketplace — GeoLicense" header="Marketplace">
    <div class="p-8 space-y-6">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Marketplace</h1>
            <p class="text-on-surface-variant text-sm mt-1">Browse software products and choose a licensing plan.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($products as $product)
                <a href="{{ route('user.marketplace.show', $product->id) }}"
                   class="group bg-surface-container rounded-2xl p-6 hover:bg-surface-container-high transition-all border border-transparent hover:border-primary/20 flex flex-col">
                    <div class="w-12 h-12 rounded-xl bg-primary-container/20 flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-primary text-2xl">deployed_code</span>
                    </div>
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-lg font-bold text-white">{{ $product->name }}</h3>
                        <span class="text-[0.625rem] font-bold text-on-surface-variant">v{{ $product->version }}</span>
                    </div>
                    <p class="text-on-surface-variant text-sm flex-1">{{ \Illuminate\Support\Str::limit($product->description, 90) }}</p>
                    <div class="mt-5 pt-4 border-t border-white/5 flex items-center justify-between">
                        <div>
                            <p class="text-[0.625rem] uppercase tracking-widest text-on-surface-variant">Starting at</p>
                            <p class="text-primary font-bold">{{ $product->min_price !== null ? money($product->min_price) : '—' }}</p>
                        </div>
                        <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors">arrow_forward</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl">inventory_2</span>
                    <p class="mt-2">No products available right now.</p>
                </div>
            @endforelse
        </div>
        @include('partials.pagination', ['paginator' => $products])
    </div>
</x-app-layout>
