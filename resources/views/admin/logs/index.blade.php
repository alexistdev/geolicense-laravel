<x-app-layout title="Log System — GeoLicense" header="Admin Console">
    @php
        $levelClasses = fn ($level) => match (strtoupper($level)) {
            'INFO' => 'bg-primary-container/20 text-primary border-primary/30',
            'WARNING' => 'bg-tertiary/10 text-tertiary border-tertiary/30',
            'ERROR', 'CRITICAL' => 'bg-error-container/40 text-error border-error/30',
            default => 'bg-surface-container-highest text-on-surface-variant border-white/10',
        };
    @endphp

    <div class="p-8 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight">Log System</h1>
                <p class="text-on-surface-variant text-sm mt-1">
                    System activity log — showing the latest 500 entries. Records are read-only and cannot be deleted.
                </p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <select name="level"
                    class="bg-surface-container border-none rounded-lg py-2.5 px-3 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none">
                    <option value="">All levels</option>
                    @foreach ($levels as $lvl)
                        <option value="{{ $lvl }}" @selected($level === $lvl)>{{ ucfirst(strtolower($lvl)) }}</option>
                    @endforeach
                </select>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                    <input name="filter" value="{{ $keyword }}" placeholder="Search message or user…"
                        class="bg-surface-container border-none rounded-lg py-2.5 pl-10 pr-4 text-sm w-full md:w-64 text-on-surface placeholder:text-on-surface-variant/50 focus:ring-1 focus:ring-primary outline-none">
                </div>
                <button type="submit"
                    class="px-4 py-2.5 rounded-lg bg-primary-container/20 text-primary text-sm font-bold hover:bg-primary-container/30 transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <div class="bg-surface-container rounded-2xl p-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-on-surface-variant text-[0.6875rem] uppercase tracking-widest border-b border-white/5">
                        <th class="py-3 whitespace-nowrap">Time</th>
                        <th class="py-3">Level</th>
                        <th class="py-3">Action</th>
                        <th class="py-3">Description</th>
                        <th class="py-3">User</th>
                        <th class="py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-white/5 align-top">
                            <td class="py-3.5 text-on-surface-variant text-xs whitespace-nowrap">
                                {{ $log->created_at?->format('d M Y H:i:s') }}
                            </td>
                            <td class="py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[0.6875rem] font-bold uppercase tracking-wider {{ $levelClasses($log->level) }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>{{ $log->level }}
                                </span>
                            </td>
                            <td class="py-3.5 text-on-surface font-medium whitespace-nowrap">{{ $log->action ?? '—' }}</td>
                            <td class="py-3.5 text-on-surface-variant max-w-md">{{ $log->description }}</td>
                            <td class="py-3.5 text-on-surface-variant text-xs whitespace-nowrap">
                                {{ $log->user?->full_name ?? $log->causer ?? 'System' }}
                            </td>
                            <td class="py-3.5 text-on-surface-variant text-xs whitespace-nowrap">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-4xl opacity-40 block mb-2">receipt_long</span>
                                No log entries recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @include('partials.pagination', ['paginator' => $logs])
        </div>
    </div>
</x-app-layout>
