@php
    /** Map a menu (bx-* icon / urlink) to a Material Symbol — ports DashboardLayout.vue mapIcon(). */
    $geoIcon = function ($menu) {
        $u = $menu->urlink ?? '';
        return match (true) {
            str_contains($u, 'dashboard') => 'dashboard',
            str_contains($u, 'users') => 'group',
            str_contains($u, 'license_types') => 'workspace_premium',
            str_contains($u, 'licenses'), str_contains($u, '/license') => 'vpn_key',
            str_contains($u, 'products') => 'inventory_2',
            str_contains($u, 'invoice') => 'receipt_long',
            str_contains($u, 'marketplace') => 'store',
            default => match (true) {
                str_contains($menu->icon ?? '', 'bx-home') => 'dashboard',
                str_contains($menu->icon ?? '', 'bx-barcode') => 'receipt_long',
                str_contains($menu->icon ?? '', 'bx-collection') => 'vpn_key',
                str_contains($menu->icon ?? '', 'bx-money') => 'payments',
                str_contains($menu->icon ?? '', 'bx-group') => 'group',
                str_contains($menu->icon ?? '', 'bx-book') => 'menu_book',
                str_contains($menu->icon ?? '', 'bx-headphone') => 'support_agent',
                str_contains($menu->icon ?? '', 'bx-store') => 'store',
                default => 'circle',
            },
        };
    };
    $isActive = fn ($url) => $url !== '#' && request()->is(ltrim($url, '/').'*');
@endphp
@props(['title' => 'GeoLicense', 'header' => 'GeoLicense'])
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'GeoLicense' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-container-lowest text-on-surface antialiased">
<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="hidden md:flex flex-col h-screen w-64 bg-[#060e20] fixed left-0 top-0 z-50">
        <div class="flex flex-col h-full py-8 space-y-2">
            <div class="px-6 mb-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-container flex items-center justify-center">
                        <span class="material-symbols-outlined text-on-primary">verified_user</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-white tracking-widest uppercase">GeoLicense</h1>
                        <p class="text-[0.6875rem] font-medium text-on-surface-variant tracking-wider uppercase">License Management</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto">
                @foreach ($parentMenus as $menu)
                    @php $children = $childMenusByParent[$menu->id] ?? collect(); @endphp
                    @if ($children->isNotEmpty())
                        <div x-data="{ open: {{ $children->contains(fn ($c) => $isActive($c->urlink)) ? 'true' : 'false' }} }">
                            <button type="button" @click="open = !open"
                                class="w-full py-3 px-6 flex items-center justify-between transition-all duration-200 cursor-pointer text-[#c2c6d6] hover:bg-[#171f33] hover:text-white border-l-4 border-transparent">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined">{{ $geoIcon($menu) }}</span>
                                    <span class="text-sm font-medium">{{ $menu->name }}</span>
                                </div>
                                <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': open }">expand_more</span>
                            </button>
                            <div x-show="open" x-collapse class="bg-[#0b1326]/50 py-2 space-y-1">
                                @foreach ($children->sortBy('sort_order') as $child)
                                    <a href="{{ $child->urlink }}"
                                        class="py-2 pl-14 pr-6 flex items-center gap-3 transition-all duration-200 {{ $isActive($child->urlink) ? 'text-blue-400 font-bold' : 'text-[#9ca3af] hover:text-white hover:bg-[#171f33]' }}">
                                        <span class="material-symbols-outlined text-[1.1rem]">{{ $geoIcon($child) }}</span>
                                        <span class="text-[0.8125rem]">{{ $child->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $menu->urlink }}"
                            class="py-3 px-6 flex items-center gap-3 transition-all duration-200 {{ $isActive($menu->urlink) ? 'bg-gradient-to-r from-blue-500/10 to-transparent text-blue-400 border-l-4 border-blue-500' : 'text-[#c2c6d6] hover:bg-[#171f33] hover:text-white border-l-4 border-transparent' }}">
                            <span class="material-symbols-outlined">{{ $geoIcon($menu) }}</span>
                            <span class="text-sm font-medium">{{ $menu->name }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            @if ($sidebarUser?->isAdmin())
                <div class="px-3 pb-2">
                    <a href="{{ route('admin.settings') }}"
                        class="py-3 px-3 flex items-center gap-3 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.settings*') ? 'bg-gradient-to-r from-blue-500/10 to-transparent text-blue-400' : 'text-[#c2c6d6] hover:bg-[#171f33] hover:text-white' }}">
                        <span class="material-symbols-outlined">settings</span>
                        <span class="text-sm font-medium">Settings</span>
                    </a>
                </div>
            @endif

            <div class="px-6 mt-auto">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full py-3 px-4 flex items-center justify-center gap-2 bg-gradient-to-br from-primary to-primary-container text-on-primary font-bold rounded-lg transition-transform active:scale-95 shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-lg">logout</span> Sign Out
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <main class="flex-1 md:ml-64 min-h-screen bg-surface-container-lowest pb-8">
        <header class="sticky top-0 z-40 w-full glass-panel border-b border-white/5 flex justify-between items-center px-6 py-3 shadow-2xl shadow-[#060e20]">
            <div class="flex items-center gap-4">
                <span class="text-lg font-extrabold tracking-tighter text-[#adc6ff]">{{ $header ?? 'GeoLicense' }}</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-white">{{ $sidebarUser?->full_name ?? 'User' }}</p>
                    <p class="text-[0.6875rem] text-primary uppercase tracking-widest">{{ $sidebarUser?->role->value }}</p>
                </div>
                <div class="w-10 h-10 rounded-full border-2 border-primary/20 bg-surface-container-high flex items-center justify-center text-primary font-bold">
                    {{ strtoupper(substr($sidebarUser?->full_name ?? 'U', 0, 1)) }}
                </div>
            </div>
        </header>

        @include('partials.flash')

        {{ $slot }}
    </main>

    {{-- Mobile bottom nav --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 glass-panel border-t border-white/5 flex justify-around items-center h-16 z-50">
        @foreach ($parentMenus->take(4) as $menu)
            <a href="{{ $menu->urlink === '#' ? ($childMenusByParent[$menu->id][0]->urlink ?? '#') : $menu->urlink }}"
               class="flex flex-col items-center gap-1 {{ $isActive($menu->urlink) ? 'text-primary' : 'text-on-surface-variant' }}">
                <span class="material-symbols-outlined">{{ $geoIcon($menu) }}</span>
                <span class="text-[10px] font-bold">{{ \Illuminate\Support\Str::limit($menu->name, 8, '') }}</span>
            </a>
        @endforeach
    </nav>
</div>
</body>
</html>
