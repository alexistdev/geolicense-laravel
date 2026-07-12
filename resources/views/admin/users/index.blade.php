<x-app-layout title="Users — GeoLicense" header="Admin Console">
    <div class="p-8 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight">Users</h1>
                <p class="text-on-surface-variant text-sm mt-1">All registered accounts on the platform.</p>
            </div>
            <form method="GET" class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                <input name="filter" value="{{ $keyword }}" placeholder="Search name or email…"
                    class="bg-surface-container border-none rounded-lg py-2.5 pl-10 pr-4 text-sm w-full md:w-72 text-on-surface placeholder:text-on-surface-variant/50 focus:ring-1 focus:ring-primary outline-none">
            </form>
        </div>

        <div class="bg-surface-container rounded-2xl p-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-on-surface-variant text-[0.6875rem] uppercase tracking-widest border-b border-white/5">
                        <th class="py-3">Full Name</th>
                        <th class="py-3">Email</th>
                        <th class="py-3">Role</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($users as $user)
                        <tr class="hover:bg-white/5">
                            <td class="py-3.5 flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-surface-container-highest flex items-center justify-center text-primary text-xs font-bold">
                                    {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                </span>
                                <span class="text-on-surface font-medium">{{ $user->full_name }}</span>
                            </td>
                            <td class="py-3.5 text-on-surface-variant">{{ $user->email }}</td>
                            <td class="py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[0.6875rem] font-bold uppercase tracking-wider {{ $user->isAdmin() ? 'bg-secondary-container/40 text-secondary' : 'bg-surface-container-highest text-on-surface-variant' }}">
                                    {{ $user->role->value }}
                                </span>
                            </td>
                            <td class="py-3.5">
                                <x-status-badge :status="$user->is_suspended ? 'SUSPENDED' : 'ACTIVE'" />
                            </td>
                            <td class="py-3.5 text-on-surface-variant text-xs">{{ $user->created_at?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-on-surface-variant">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @include('partials.pagination', ['paginator' => $users])
        </div>
    </div>
</x-app-layout>
