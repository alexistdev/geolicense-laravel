<x-app-layout title="Pay {{ $invoice->invoice_number }} — GeoLicense" header="My Account">
    <div class="p-8 space-y-6 max-w-2xl">
        <a href="{{ route('user.invoice.show', $invoice->id) }}" class="inline-flex items-center gap-1 text-on-surface-variant hover:text-primary text-sm">
            <span class="material-symbols-outlined text-base">arrow_back</span> Back to invoice
        </a>

        <div class="bg-surface-container rounded-2xl p-8 space-y-6">
            <div>
                <h1 class="text-2xl font-black text-white">Complete Payment</h1>
                <p class="text-on-surface-variant text-sm mt-1">Transfer the exact total, then submit your payment reference for verification.</p>
            </div>

            <div class="rounded-xl bg-gradient-to-br from-primary-container/20 to-surface-container-high p-6">
                <p class="text-xs uppercase tracking-widest text-on-surface-variant">Amount to transfer</p>
                <p class="text-4xl font-black text-white mt-1">{{ money($invoice->total_amount, $invoice->currency) }}</p>
                <p class="text-xs text-tertiary mt-2">Includes unique code {{ $invoice->unique_code }} — transfer the exact amount.</p>
            </div>

            @if ($bankAccount)
                <div class="rounded-xl bg-surface-container-high p-6">
                    <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-3">Transfer to</p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><p class="text-on-surface-variant text-xs">Bank</p><p class="text-on-surface font-bold">{{ $bankAccount->bank_name }}</p></div>
                        <div><p class="text-on-surface-variant text-xs">Account No.</p><p class="text-on-surface font-mono">{{ $bankAccount->account_number }}</p></div>
                        <div class="col-span-2"><p class="text-on-surface-variant text-xs">Account Holder</p><p class="text-on-surface">{{ $bankAccount->account_holder }}</p></div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('user.invoice.payment.submit', $invoice->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1.5">Payment Method / Provider</label>
                    <input name="provider" value="{{ old('provider', $bankAccount?->bank_name ?? 'Bank Transfer') }}" required
                        class="w-full bg-surface-container-highest/50 rounded-lg py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary/50 outline-none border-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-widest text-on-surface-variant mb-1.5">Payment Reference / Proof</label>
                    <input name="provider_reference" value="{{ old('provider_reference') }}" required placeholder="e.g. transfer ID, sender name, or receipt no."
                        class="w-full bg-surface-container-highest/50 rounded-lg py-3 px-4 text-on-surface placeholder:text-on-surface-variant/40 focus:ring-2 focus:ring-primary/50 outline-none border-none">
                </div>
                <button type="submit" class="w-full py-3.5 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">task_alt</span> Submit Payment for Verification
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
