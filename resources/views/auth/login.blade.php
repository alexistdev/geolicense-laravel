<x-guest-layout title="Login — GeoLicense">
<div class="min-h-screen flex flex-col items-center justify-center antialiased overflow-hidden relative" x-data="{ show: false }">
    <div class="fixed inset-0 tech-pattern z-0"></div>
    <div class="fixed top-[-10%] left-[-10%] w-1/2 h-1/2 bg-primary/5 blur-[120px] rounded-full z-0"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-1/2 h-1/2 bg-secondary-container/10 blur-[120px] rounded-full z-0"></div>

    <main class="relative z-10 w-full max-w-md px-6 py-12 flex flex-col items-center">
        <div class="mb-12 text-center">
            <div class="flex items-center justify-center gap-3 mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-primary to-primary-container rounded-xl flex items-center justify-center shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-on-primary text-3xl" style="font-variation-settings: 'FILL' 1">security</span>
                </div>
            </div>
            <h1 class="text-3xl font-black tracking-tighter text-slate-100 mb-1 uppercase">GeoLicense</h1>
            <p class="text-on-surface-variant text-sm tracking-widest font-medium uppercase opacity-70">Management License</p>
        </div>

        <div class="w-full bg-surface-container/60 backdrop-blur-2xl p-8 rounded-xl shadow-2xl shadow-black/40 border-t border-white/5 relative">
            <div class="absolute inset-0 bg-gradient-to-b from-white/5 to-transparent rounded-xl pointer-events-none"></div>
            <div class="relative z-10">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-on-surface tracking-tight">LOGIN</h2>
                    <p class="text-on-surface-variant text-sm mt-1">Enter your email and password to authenticate</p>
                </div>

                @if ($errors->any())
                    <div class="bg-error-container text-on-error-container px-4 py-3 rounded-lg mb-6 text-sm font-medium">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="email">Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">alternate_email</span>
                            <input value="{{ old('email') }}" name="email" type="email" id="email" placeholder="email@mail.com" required
                                class="w-full bg-surface-container-highest/50 border-none rounded-lg py-3.5 pl-12 pr-4 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/50 transition-all outline-none">
                        </div>
                    </div>

                    <div class="space-y-2" x-data="{ show: false }">
                        <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant" for="password">Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">lock</span>
                            <input name="password" :type="show ? 'text' : 'password'" id="password" placeholder="••••••••••••" required
                                class="w-full bg-surface-container-highest/50 border-none rounded-lg py-3.5 pl-12 pr-12 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/50 transition-all outline-none">
                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-xl" x-text="show ? 'visibility_off' : 'visibility'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input name="remember" type="checkbox" class="w-5 h-5 rounded border border-outline-variant/30 bg-surface-container-highest text-primary focus:ring-primary/40">
                            <span class="text-sm text-on-surface-variant">Remember</span>
                        </label>
                        <a href="#" class="text-sm font-semibold text-primary hover:text-primary-container transition-all">Recovery Password?</a>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold rounded-lg shadow-lg shadow-primary/20 hover:scale-[1.01] active:scale-[0.98] transition-all uppercase tracking-widest text-xs flex items-center justify-center gap-2">
                        LOGIN <span class="material-symbols-outlined text-lg">login</span>
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-on-surface-variant">
                    No account yet?
                    <a href="{{ route('register') }}" class="font-semibold text-primary hover:text-primary-container">Register</a>
                </p>
            </div>
        </div>

        <div class="mt-8 flex items-center gap-2 px-4 py-2 bg-surface-container-low/80 rounded-full border border-white/5">
            <span class="w-2 h-2 rounded-full bg-green-300 animate-pulse"></span>
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant">Status: ONLINE</span>
        </div>

        <div class="mt-10 w-full bg-surface-container-low/50 rounded-lg border border-white/5 p-4 text-xs text-on-surface-variant">
            <p class="font-bold uppercase tracking-widest text-[0.65rem] text-primary mb-2">Demo Accounts</p>
            <p>Admin — <span class="text-on-surface">alexistdev@gmail.com</span> / <span class="text-on-surface">1234</span></p>
            <p>User — <span class="text-on-surface">user@gmail.com</span> / <span class="text-on-surface">1234</span></p>
        </div>
    </main>

    <footer class="relative z-10 w-full py-6 flex flex-col md:flex-row justify-center items-center px-12 gap-2 mt-8">
        <div class="text-slate-500 text-xs uppercase tracking-widest">© {{ date('Y') }} GeoLicense · Created by AlexistDev</div>
    </footer>
</div>
</x-guest-layout>
