<x-app-layout title="{{ $user->full_name }} — GeoLicense" header="Admin Console">
    <div class="p-8 space-y-6 max-w-4xl" x-data="{ suspendOpen: false, reactivateOpen: false }">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-on-surface-variant hover:text-primary text-sm">
            <span class="material-symbols-outlined text-base">arrow_back</span> Back to users
        </a>

        <div class="bg-surface-container rounded-2xl p-8">
            {{-- Header --}}
            <div class="flex flex-col md:flex-row justify-between gap-4 pb-6 border-b border-white/5">
                <div class="flex items-center gap-4">
                    <span class="w-14 h-14 rounded-full bg-surface-container-highest flex items-center justify-center text-primary text-xl font-black">
                        {{ strtoupper(substr($user->full_name, 0, 1)) }}
                    </span>
                    <div>
                        <h1 class="text-2xl font-black text-white">{{ $user->full_name }}</h1>
                        <p class="text-on-surface-variant text-sm">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="flex flex-col items-start md:items-end gap-2">
                    <x-status-badge :status="$user->is_suspended ? 'SUSPENDED' : 'ACTIVE'" />
                    <span class="px-2.5 py-1 rounded-full text-[0.6875rem] font-bold uppercase tracking-wider {{ $user->isAdmin() ? 'bg-secondary-container/40 text-secondary' : 'bg-surface-container-highest text-on-surface-variant' }}">
                        {{ $user->role->value }}
                    </span>
                </div>
            </div>

            {{-- Meta --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 py-6 text-sm">
                <div>
                    <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-1">Licenses</p>
                    <p class="text-on-surface font-semibold text-lg">{{ $user->licenses_count }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-1">Orders</p>
                    <p class="text-on-surface font-semibold text-lg">{{ $user->orders_count }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-1">Joined</p>
                    <p class="text-on-surface">{{ $user->created_at?->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-1">User ID</p>
                    <p class="font-mono text-primary text-xs break-all">{{ $user->id }}</p>
                </div>
            </div>

            {{-- Recent licenses --}}
            <div class="border-t border-white/5 py-6">
                <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-4">Recent Licenses</p>
                <div class="space-y-3">
                    @forelse ($licenses as $license)
                        <div class="flex justify-between items-center p-4 rounded-lg bg-surface-container-high">
                            <div>
                                <p class="text-on-surface font-medium">{{ $license->product?->name ?? '—' }}</p>
                                <p class="text-on-surface-variant text-xs font-mono">{{ $license->license_key }}</p>
                            </div>
                            <x-status-badge :status="$license->status" />
                        </div>
                    @empty
                        <p class="text-on-surface-variant text-sm">No licenses issued yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent orders --}}
            <div class="border-t border-white/5 py-6">
                <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-4">Recent Orders</p>
                <div class="space-y-3">
                    @forelse ($orders as $order)
                        <div class="flex justify-between items-center p-4 rounded-lg bg-surface-container-high">
                            <div>
                                <p class="font-mono text-primary text-sm">{{ $order->order_number }}</p>
                                <p class="text-on-surface-variant text-xs">{{ $order->order_items_count }} item(s) · {{ $order->created_at?->format('d M Y') }}</p>
                            </div>
                            <x-status-badge :status="$order->status" />
                        </div>
                    @empty
                        <p class="text-on-surface-variant text-sm">No orders yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Actions --}}
            @unless ($user->is(auth()->user()))
                <div class="border-t border-white/5 pt-6 flex flex-wrap gap-3">
                    @if ($user->is_suspended)
                        <button type="button" @click="reactivateOpen = true"
                            class="flex items-center gap-2 px-5 py-3 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-sm">
                            <span class="material-symbols-outlined text-lg">lock_open</span> Reactivate Account
                        </button>
                    @else
                        <button type="button" @click="suspendOpen = true"
                            class="flex items-center gap-2 px-5 py-3 rounded-lg bg-error-container/40 text-error border border-error/30 font-bold text-sm hover:bg-error-container/60">
                            <span class="material-symbols-outlined text-lg">block</span> Suspend Account
                        </button>
                    @endif
                </div>
            @endunless
        </div>

        {{-- Suspend confirmation modal --}}
        @unless ($user->is(auth()->user()))
            <div x-show="suspendOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display:none">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="suspendOpen = false"></div>
                <div class="relative w-full max-w-md bg-surface-container rounded-2xl border border-white/10 shadow-2xl p-6"
                     @keydown.escape.window="suspendOpen = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-11 h-11 rounded-full bg-error-container/30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-error">block</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white">Suspend this account?</h3>
                            <p class="text-sm text-on-surface-variant mt-1.5">
                                <span class="font-semibold text-on-surface">{{ $user->full_name }}</span> will be blocked from signing in
                                until the account is reactivated. You can undo this at any time.
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="suspendOpen = false"
                            class="px-4 py-2.5 rounded-lg bg-surface-container-high text-on-surface text-sm font-medium">
                            Cancel
                        </button>
                        <form method="POST" action="{{ route('admin.users.suspend', $user->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="flex items-center gap-2 px-5 py-2.5 rounded-lg bg-error text-on-error font-bold text-sm">
                                <span class="material-symbols-outlined text-lg">block</span> Suspend Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Reactivate confirmation modal --}}
            <div x-show="reactivateOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display:none">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="reactivateOpen = false"></div>
                <div class="relative w-full max-w-md bg-surface-container rounded-2xl border border-white/10 shadow-2xl p-6"
                     @keydown.escape.window="reactivateOpen = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-11 h-11 rounded-full bg-primary-container/30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">lock_open</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white">Reactivate this account?</h3>
                            <p class="text-sm text-on-surface-variant mt-1.5">
                                <span class="font-semibold text-on-surface">{{ $user->full_name }}</span> will be able to sign in
                                and use the platform again.
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="reactivateOpen = false"
                            class="px-4 py-2.5 rounded-lg bg-surface-container-high text-on-surface text-sm font-medium">
                            Cancel
                        </button>
                        <form method="POST" action="{{ route('admin.users.suspend', $user->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="flex items-center gap-2 px-5 py-2.5 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-sm">
                                <span class="material-symbols-outlined text-lg">lock_open</span> Reactivate Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endunless
    </div>
</x-app-layout>
