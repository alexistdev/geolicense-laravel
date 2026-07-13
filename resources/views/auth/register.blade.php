<x-guest-layout title="Register — GeoLicense">
<div class="min-h-screen flex flex-col items-center justify-center antialiased overflow-hidden relative">
    <div class="fixed inset-0 tech-pattern z-0"></div>
    <div class="fixed top-[-10%] left-[-10%] w-1/2 h-1/2 bg-primary/5 blur-[120px] rounded-full z-0"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-1/2 h-1/2 bg-secondary-container/10 blur-[120px] rounded-full z-0"></div>

    <main class="relative z-10 w-full max-w-md px-6 py-12 flex flex-col items-center">
        <div class="mb-10 text-center">
            <div class="flex items-center justify-center gap-3 mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-primary to-primary-container rounded-xl flex items-center justify-center shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-on-primary text-3xl" style="font-variation-settings: 'FILL' 1">person_add</span>
                </div>
            </div>
            <h1 class="text-3xl font-black tracking-tighter text-slate-100 mb-1 uppercase">GeoLicense</h1>
            <p class="text-on-surface-variant text-sm tracking-widest font-medium uppercase opacity-70">Create Account</p>
        </div>

        <div class="w-full bg-surface-container/60 backdrop-blur-2xl p-8 rounded-xl shadow-2xl shadow-black/40 border-t border-white/5 relative">
            <div class="relative z-10">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-on-surface tracking-tight">REGISTER</h2>
                    <p class="text-on-surface-variant text-sm mt-1">Fill in your details to create a user account</p>
                </div>

                @if ($errors->any())
                    <div class="bg-error-container text-on-error-container px-4 py-3 rounded-lg mb-6 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="full_name">Full Name</label>
                        <input value="{{ old('full_name') }}" name="full_name" id="full_name" type="text" required
                            class="w-full bg-surface-container-highest/50 border-none rounded-lg py-3.5 px-4 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/50 outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="email">Email</label>
                        <input value="{{ old('email') }}" name="email" id="email" type="email" required
                            class="w-full bg-surface-container-highest/50 border-none rounded-lg py-3.5 px-4 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/50 outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="password">Password</label>
                        <input name="password" id="password" type="password" required
                            class="w-full bg-surface-container-highest/50 border-none rounded-lg py-3.5 px-4 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/50 outline-none">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="password_confirmation">Confirm Password</label>
                        <input name="password_confirmation" id="password_confirmation" type="password" required
                            class="w-full bg-surface-container-highest/50 border-none rounded-lg py-3.5 px-4 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/50 outline-none">
                    </div>

                    <x-recaptcha action="register" />

                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold rounded-lg shadow-lg shadow-primary/20 hover:scale-[1.01] active:scale-[0.98] transition-all uppercase tracking-widest text-xs flex items-center justify-center gap-2">
                        Create Account <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-on-surface-variant">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-primary-container">Login</a>
                </p>
            </div>
        </div>
    </main>
</div>
</x-guest-layout>
