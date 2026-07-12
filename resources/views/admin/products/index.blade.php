<x-app-layout title="Products — GeoLicense" header="Admin Console">
    <div class="p-8 space-y-6" x-data="{
        open: false,
        mode: 'create',
        form: { id: '', name: '', version: '', sku: '', description: '', is_active: true },
        storeUrl: '{{ route('admin.products.store') }}',
        base: '{{ url('admin/products') }}',
        openCreate() { this.mode = 'create'; this.form = { id: '', name: '', version: '', sku: '', description: '', is_active: true }; this.open = true; },
        openEdit(p) { this.mode = 'edit'; this.form = { ...p }; this.open = true; },
        get action() { return this.mode === 'create' ? this.storeUrl : this.base + '/' + this.form.id; }
    }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight">Products</h1>
                <p class="text-on-surface-variant text-sm mt-1">Software products available for licensing.</p>
            </div>
            <div class="flex gap-3">
                <form method="GET" class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                    <input name="filter" value="{{ $keyword }}" placeholder="Search…"
                        class="bg-surface-container border-none rounded-lg py-2.5 pl-10 pr-4 text-sm w-full md:w-56 text-on-surface placeholder:text-on-surface-variant/50 focus:ring-1 focus:ring-primary outline-none">
                </form>
                <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-sm whitespace-nowrap">
                    <span class="material-symbols-outlined text-lg">add</span> Add Product
                </button>
            </div>
        </div>

        <div class="bg-surface-container rounded-2xl p-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-on-surface-variant text-[0.6875rem] uppercase tracking-widest border-b border-white/5">
                        <th class="py-3">Name</th>
                        <th class="py-3">SKU</th>
                        <th class="py-3">Version</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($products as $product)
                        <tr class="hover:bg-white/5">
                            <td class="py-3.5">
                                <p class="text-on-surface font-medium">{{ $product->name }}</p>
                                <p class="text-on-surface-variant text-xs">{{ \Illuminate\Support\Str::limit($product->description, 50) }}</p>
                            </td>
                            <td class="py-3.5 font-mono text-xs text-primary">{{ $product->sku }}</td>
                            <td class="py-3.5 text-on-surface-variant">v{{ $product->version }}</td>
                            <td class="py-3.5"><x-status-badge :status="$product->is_active ? 'ACTIVE' : 'SUSPENDED'" /></td>
                            <td class="py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEdit({{ \Illuminate\Support\Js::from(['id' => $product->id, 'name' => $product->name, 'version' => $product->version, 'sku' => $product->sku, 'description' => $product->description, 'is_active' => (bool) $product->is_active]) }})"
                                        class="p-2 rounded-lg hover:bg-surface-container-high text-on-surface-variant hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" onsubmit="return confirm('Delete this product?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg hover:bg-error-container/40 text-on-surface-variant hover:text-error transition-colors">
                                            <span class="material-symbols-outlined text-lg">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-on-surface-variant">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @include('partials.pagination', ['paginator' => $products])
        </div>

        {{-- Create / edit modal --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display:none">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative w-full max-w-lg bg-surface-container rounded-2xl border border-white/10 shadow-2xl p-6" @keydown.escape.window="open = false">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-white" x-text="mode === 'create' ? 'Add Product' : 'Edit Product'"></h3>
                    <button @click="open = false" class="text-on-surface-variant hover:text-white"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form method="POST" :action="action" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" :value="mode === 'create' ? 'POST' : 'PUT'">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1.5">Name</label>
                        <input name="name" x-model="form.name" required class="w-full bg-surface-container-highest/50 rounded-lg py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary/50 outline-none border-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1.5">SKU</label>
                            <input name="sku" x-model="form.sku" required class="w-full bg-surface-container-highest/50 rounded-lg py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary/50 outline-none border-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1.5">Version</label>
                            <input name="version" x-model="form.version" required class="w-full bg-surface-container-highest/50 rounded-lg py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary/50 outline-none border-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1.5">Description</label>
                        <textarea name="description" x-model="form.description" rows="3" class="w-full bg-surface-container-highest/50 rounded-lg py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary/50 outline-none border-none"></textarea>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="w-5 h-5 rounded bg-surface-container-highest text-primary border-none focus:ring-primary/40">
                        <span class="text-sm text-on-surface-variant">Active (available for licensing)</span>
                    </label>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2.5 rounded-lg bg-surface-container-high text-on-surface text-sm font-medium">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary text-sm font-bold">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
