<x-guest-layout title="Reset Password — GeoLicense">
<div class="min-h-screen flex flex-col items-center justify-center antialiased overflow-hidden relative">
    <div class="fixed inset-0 tech-pattern z-0"></div>
    <div class="fixed top-[-10%] left-[-10%] w-1/2 h-1/2 bg-primary/5 blur-[120px] rounded-full z-0"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-1/2 h-1/2 bg-secondary-container/10 blur-[120px] rounded-full z-0"></div>

    <main class="relative z-10 w-full max-w-md px-6 py-12 flex flex-col items-center">
        <div class="mb-10 text-center">
            <div class="flex items-center justify-center gap-3 mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-primary to-primary-container rounded-xl flex items-center justify-center shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-on-primary text-3xl" style="font-variation-settings: 'FILL' 1">password</span>
                </div>
            </div>
            <h1 class="text-3xl font-black tracking-tighter text-slate-100 mb-1 uppercase">GeoLicense</h1>
            <p class="text-on-surface-variant text-sm tracking-widest font-medium uppercase opacity-70">Set New Password</p>
        </div>

        <div class="w-full bg-surface-container/60 backdrop-blur-2xl p-8 rounded-xl shadow-2xl shadow-black/40 border-t border-white/5 relative">
            <div class="relative z-10">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-on-surface tracking-tight">RESET PASSWORD</h2>
                    <p class="text-on-surface-variant text-sm mt-1">Choose a new password for your account.</p>
                </div>

                @if ($errors->any())
                    <div class="bg-error-container text-on-error-container px-4 py-3 rounded-lg mb-6 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-5" x-data="{ show: false }">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="email">Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">alternate_email</span>
                            <input value="{{ old('email', $email) }}" name="email" type="email" id="email" required
                                class="w-full bg-surface-container-highest/50 border-none rounded-lg py-3.5 pl-12 pr-4 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/50 transition-all outline-none">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="password">New Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">lock</span>
                            <input name="password" :type="show ? 'text' : 'password'" id="password" placeholder="••••••••••••" required autocomplete="new-password"
                                class="w-full bg-surface-container-highest/50 border-none rounded-lg py-3.5 pl-12 pr-12 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/50 transition-all outline-none">
                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="password_confirmation">Confirm Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">lock</span>
                            <input name="password_confirmation" :type="show ? 'text' : 'password'" id="password_confirmation" placeholder="••••••••••••" required autocomplete="new-password"
                                class="w-full bg-surface-container-highest/50 border-none rounded-lg py-3.5 pl-12 pr-4 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/50 transition-all outline-none">
                        </div>
                    </div>

                    <x-recaptcha action="reset_password" />

                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold rounded-lg shadow-lg shadow-primary/20 hover:scale-[1.01] active:scale-[0.98] transition-all uppercase tracking-widest text-xs flex items-center justify-center gap-2">
                        Reset Password <span class="material-symbols-outlined text-lg">check</span>
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>
</x-guest-layout>
