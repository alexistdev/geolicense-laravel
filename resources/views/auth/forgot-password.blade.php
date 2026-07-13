<x-guest-layout title="Recovery Password — GeoLicense">
<div class="min-h-screen flex flex-col items-center justify-center antialiased overflow-hidden relative">
    <div class="fixed inset-0 tech-pattern z-0"></div>
    <div class="fixed top-[-10%] left-[-10%] w-1/2 h-1/2 bg-primary/5 blur-[120px] rounded-full z-0"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-1/2 h-1/2 bg-secondary-container/10 blur-[120px] rounded-full z-0"></div>

    <main class="relative z-10 w-full max-w-md px-6 py-12 flex flex-col items-center">
        <div class="mb-10 text-center">
            <div class="flex items-center justify-center gap-3 mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-primary to-primary-container rounded-xl flex items-center justify-center shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-on-primary text-3xl" style="font-variation-settings: 'FILL' 1">lock_reset</span>
                </div>
            </div>
            <h1 class="text-3xl font-black tracking-tighter text-slate-100 mb-1 uppercase">GeoLicense</h1>
            <p class="text-on-surface-variant text-sm tracking-widest font-medium uppercase opacity-70">Recover Password</p>
        </div>

        <div class="w-full bg-surface-container/60 backdrop-blur-2xl p-8 rounded-xl shadow-2xl shadow-black/40 border-t border-white/5 relative">
            <div class="relative z-10">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-on-surface tracking-tight">FORGOT PASSWORD</h2>
                    <p class="text-on-surface-variant text-sm mt-1">Enter your email and we'll send you a reset link.</p>
                </div>

                @if (session('status'))
                    <div class="bg-primary-container/40 border border-primary/30 text-primary px-4 py-3 rounded-lg mb-6 text-sm font-medium">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-error-container text-on-error-container px-4 py-3 rounded-lg mb-6 text-sm font-medium">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="email">Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">alternate_email</span>
                            <input value="{{ old('email') }}" name="email" type="email" id="email" placeholder="email@mail.com" required
                                class="w-full bg-surface-container-highest/50 border-none rounded-lg py-3.5 pl-12 pr-4 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/50 transition-all outline-none">
                        </div>
                    </div>

                    <x-recaptcha action="forgot_password" />

                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold rounded-lg shadow-lg shadow-primary/20 hover:scale-[1.01] active:scale-[0.98] transition-all uppercase tracking-widest text-xs flex items-center justify-center gap-2">
                        Send Reset Link <span class="material-symbols-outlined text-lg">send</span>
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-on-surface-variant">
                    Remembered it?
                    <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-primary-container">Back to Login</a>
                </p>
            </div>
        </div>
    </main>
</div>
</x-guest-layout>
