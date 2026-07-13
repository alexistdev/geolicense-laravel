<x-app-layout title="License Types — GeoLicense" header="Admin Console">
    <div class="p-8 space-y-6" x-data="{
        open: false,
        mode: 'create',
        form: { id: '', name: '', description: '', is_trial: false },
        confirmOpen: false,
        deleteTarget: { id: '', name: '' },
        storeUrl: '{{ route('admin.license-types.store') }}',
        base: '{{ url('admin/license_types') }}',
        openCreate() { this.mode = 'create'; this.form = { id: '', name: '', description: '', is_trial: false }; this.open = true; },
        openEdit(t) { this.mode = 'edit'; this.form = { ...t }; this.open = true; },
        openDelete(t) { this.deleteTarget = { ...t }; this.confirmOpen = true; },
        get action() { return this.mode === 'create' ? this.storeUrl : this.base + '/' + this.form.id; },
        get deleteAction() { return this.base + '/' + this.deleteTarget.id; }
    }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight">License Types</h1>
                <p class="text-on-surface-variant text-sm mt-1">Categories used when building license plans.</p>
            </div>
            <div class="flex gap-3">
                <form method="GET" class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                    <input name="filter" value="{{ $keyword }}" placeholder="Search…"
                        class="bg-surface-container border-none rounded-lg py-2.5 pl-10 pr-4 text-sm w-full md:w-56 text-on-surface placeholder:text-on-surface-variant/50 focus:ring-1 focus:ring-primary outline-none">
                </form>
                <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-sm whitespace-nowrap">
                    <span class="material-symbols-outlined text-lg">add</span> Add Type
                </button>
            </div>
        </div>

        <div class="bg-surface-container rounded-2xl p-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-on-surface-variant text-[0.6875rem] uppercase tracking-widest border-b border-white/5">
                        <th class="py-3">Name</th>
                        <th class="py-3">Description</th>
                        <th class="py-3">Trial</th>
                        <th class="py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($licenseTypes as $type)
                        <tr class="hover:bg-white/5">
                            <td class="py-3.5 text-on-surface font-medium">{{ $type->name }}</td>
                            <td class="py-3.5 text-on-surface-variant">{{ \Illuminate\Support\Str::limit($type->description, 60) }}</td>
                            <td class="py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[0.6875rem] font-bold uppercase {{ $type->is_trial ? 'bg-tertiary/10 text-tertiary' : 'bg-surface-container-highest text-on-surface-variant' }}">
                                    {{ $type->is_trial ? 'Trial' : 'Paid' }}
                                </span>
                            </td>
                            <td class="py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="openEdit({{ \Illuminate\Support\Js::from(['id' => $type->id, 'name' => $type->name, 'description' => $type->description, 'is_trial' => (bool) $type->is_trial]) }})"
                                        class="p-2 rounded-lg hover:bg-surface-container-high text-on-surface-variant hover:text-primary transition-colors">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <button type="button" @click="openDelete({{ \Illuminate\Support\Js::from(['id' => $type->id, 'name' => $type->name]) }})"
                                        class="p-2 rounded-lg hover:bg-error-container/40 text-on-surface-variant hover:text-error transition-colors">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-10 text-center text-on-surface-variant">No license types found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @include('partials.pagination', ['paginator' => $licenseTypes])
        </div>

        <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="open = false"></div>
            <div class="relative w-full max-w-lg bg-surface-container rounded-2xl border border-white/10 shadow-2xl p-6" @keydown.escape.window="open = false">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-white" x-text="mode === 'create' ? 'Add License Type' : 'Edit License Type'"></h3>
                    <button @click="open = false" class="text-on-surface-variant hover:text-white"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form method="POST" :action="action" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" :value="mode === 'create' ? 'POST' : 'PUT'">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1.5">Name</label>
                        <input name="name" x-model="form.name" required class="w-full bg-surface-container-highest/50 rounded-lg py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary/50 outline-none border-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1.5">Description</label>
                        <textarea name="description" x-model="form.description" rows="3" class="w-full bg-surface-container-highest/50 rounded-lg py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary/50 outline-none border-none"></textarea>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_trial" value="1" x-model="form.is_trial" class="w-5 h-5 rounded bg-surface-container-highest text-primary border-none focus:ring-primary/40">
                        <span class="text-sm text-on-surface-variant">Trial license type</span>
                    </label>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2.5 rounded-lg bg-surface-container-high text-on-surface text-sm font-medium">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary text-sm font-bold">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete confirmation modal --}}
        <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="confirmOpen = false"></div>
            <div class="relative w-full max-w-md bg-surface-container rounded-2xl border border-white/10 shadow-2xl p-6" @keydown.escape.window="confirmOpen = false">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-11 h-11 rounded-full bg-error-container/30 flex items-center justify-center">
                        <span class="material-symbols-outlined text-error">warning</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-white">Delete License Type</h3>
                        <p class="text-sm text-on-surface-variant mt-1">
                            Are you sure you want to delete <span class="font-semibold text-on-surface" x-text="deleteTarget.name"></span>?
                            This action cannot be undone.
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <button type="button" @click="confirmOpen = false" class="px-4 py-2.5 rounded-lg bg-surface-container-high text-on-surface text-sm font-medium">Cancel</button>
                    <form method="POST" :action="deleteAction">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-error text-on-error text-sm font-bold">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
