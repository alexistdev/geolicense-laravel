@if (session('success') || session('error') || $errors->any())
    <div class="px-8 pt-6 space-y-3">
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 class="flex items-center gap-3 bg-primary-container/20 border border-primary/30 text-primary px-4 py-3 rounded-lg text-sm font-medium">
                <span class="material-symbols-outlined text-lg">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 bg-error-container/40 border border-error/30 text-error px-4 py-3 rounded-lg text-sm font-medium">
                <span class="material-symbols-outlined text-lg">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-error-container/40 border border-error/30 text-error px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
