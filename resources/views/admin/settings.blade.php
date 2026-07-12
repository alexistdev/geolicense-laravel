<x-app-layout title="Settings — GeoLicense" header="Admin Console">
    <div class="p-8 space-y-8 max-w-3xl">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Settings</h1>
            <p class="text-on-surface-variant text-sm mt-1">Manage your account email and password.</p>
        </div>

        {{-- Profile / email --}}
        <div class="bg-surface-container rounded-2xl p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined text-primary">account_circle</span>
                <div>
                    <h2 class="text-lg font-bold text-white">Profile</h2>
                    <p class="text-xs text-on-surface-variant">Update your name and login email.</p>
                </div>
            </div>

            @if ($errors->profile->any())
                <div class="mb-5 bg-error-container/40 border border-error/30 text-error px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->profile->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.profile') }}" class="space-y-5">
                @csrf
                @method('PATCH')
                <div>
                    <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">Full Name</label>
                    <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $user->full_name) }}" required
                        class="w-full bg-surface-container-high border-none rounded-lg py-2.5 px-4 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                        class="w-full bg-surface-container-high border-none rounded-lg py-2.5 px-4 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div class="pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 py-2.5 px-5 bg-gradient-to-br from-primary to-primary-container text-on-primary font-bold text-sm rounded-lg transition-transform active:scale-95 shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-lg">save</span> Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Password --}}
        <div class="bg-surface-container rounded-2xl p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined text-primary">lock</span>
                <div>
                    <h2 class="text-lg font-bold text-white">Password</h2>
                    <p class="text-xs text-on-surface-variant">Use a strong password you don't reuse elsewhere.</p>
                </div>
            </div>

            @if ($errors->password->any())
                <div class="mb-5 bg-error-container/40 border border-error/30 text-error px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->password->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.password') }}" class="space-y-5">
                @csrf
                @method('PATCH')
                <div>
                    <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">Current Password</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                        class="w-full bg-surface-container-high border-none rounded-lg py-2.5 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">New Password</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                        class="w-full bg-surface-container-high border-none rounded-lg py-2.5 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-2">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                        class="w-full bg-surface-container-high border-none rounded-lg py-2.5 px-4 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div class="pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 py-2.5 px-5 bg-gradient-to-br from-primary to-primary-container text-on-primary font-bold text-sm rounded-lg transition-transform active:scale-95 shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-lg">key</span> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
