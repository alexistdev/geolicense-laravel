<x-app-layout title="Invoice {{ $invoice->invoice_number }} — GeoLicense" header="Admin Console">
    @php $payment = $invoice->order?->payment; @endphp
    <div class="p-8 space-y-6 max-w-4xl">
        <a href="{{ route('admin.invoices.index') }}" class="inline-flex items-center gap-1 text-on-surface-variant hover:text-primary text-sm">
            <span class="material-symbols-outlined text-base">arrow_back</span> Back to invoices
        </a>

        <div class="bg-surface-container rounded-2xl p-8">
            <div class="flex flex-col md:flex-row justify-between gap-4 pb-6 border-b border-white/5">
                <div>
                    <p class="text-on-surface-variant text-xs uppercase tracking-widest">Invoice</p>
                    <h1 class="text-2xl font-black text-white mt-1">{{ $invoice->invoice_number }}</h1>
                    <p class="text-on-surface-variant text-sm mt-1">Issued {{ $invoice->issued_at?->format('d M Y, H:i') }}</p>
                </div>
                <div class="text-right">
                    <x-status-badge :status="$invoice->status" />
                    <p class="text-3xl font-black text-white mt-3">{{ money($invoice->total_amount, $invoice->currency) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-6">
                <div>
                    <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-2">Customer</p>
                    <p class="text-on-surface font-medium">{{ $invoice->order?->user?->full_name }}</p>
                    <p class="text-on-surface-variant text-sm">{{ $invoice->order?->user?->email }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-2">Order</p>
                    <p class="font-mono text-primary text-sm">{{ $invoice->order?->order_number }}</p>
                    <p class="text-on-surface-variant text-sm">Status: {{ $invoice->order?->status?->value }}</p>
                </div>
            </div>

            <div class="border-t border-white/5 py-6">
                <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-4">Line Items</p>
                <div class="space-y-3">
                    @foreach ($invoice->order?->orderItems ?? [] as $item)
                        <div class="flex justify-between items-center p-4 rounded-lg bg-surface-container-high">
                            <div>
                                <p class="text-on-surface font-medium">{{ $item->licensePlan?->name }}</p>
                                <p class="text-on-surface-variant text-xs">{{ $item->licensePlan?->product?->name }} · Qty {{ $item->quantity }}</p>
                            </div>
                            <p class="text-on-surface font-semibold">{{ money($item->total_price, $invoice->currency) }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between mt-4 text-sm text-on-surface-variant">
                    <span>Unique code</span><span>{{ $invoice->unique_code }}</span>
                </div>
                <div class="flex justify-between mt-2 text-lg font-bold text-white">
                    <span>Total</span><span>{{ money($invoice->total_amount, $invoice->currency) }}</span>
                </div>
            </div>

            {{-- Payment proof --}}
            @if ($payment)
                <div class="border-t border-white/5 py-6">
                    <p class="text-xs uppercase tracking-widest text-on-surface-variant mb-4">Payment</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div><p class="text-on-surface-variant text-xs">Provider</p><p class="text-on-surface">{{ $payment->provider }}</p></div>
                        <div><p class="text-on-surface-variant text-xs">Reference</p><p class="text-on-surface font-mono text-xs">{{ $payment->provider_reference }}</p></div>
                        <div><p class="text-on-surface-variant text-xs">Amount</p><p class="text-on-surface">{{ money($payment->amount, $payment->currency) }}</p></div>
                        <div><p class="text-on-surface-variant text-xs">Status</p><x-status-badge :status="$payment->status" /></div>
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            @if ($invoice->status->canTransitionTo(\App\Enums\InvoiceStatus::PAID) || $invoice->status->canTransitionTo(\App\Enums\InvoiceStatus::UNPAID))
                <div class="border-t border-white/5 pt-6 flex flex-wrap gap-3">
                    @if ($invoice->status->canTransitionTo(\App\Enums\InvoiceStatus::PAID))
                        <form method="POST" action="{{ route('admin.invoices.validate', $invoice->id) }}" onsubmit="return confirm('Approve this payment and issue the license?')">
                            @csrf @method('PATCH')
                            <button class="flex items-center gap-2 px-5 py-3 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-sm">
                                <span class="material-symbols-outlined text-lg">check_circle</span> Approve &amp; Issue License
                            </button>
                        </form>
                    @endif
                    @if ($invoice->status === \App\Enums\InvoiceStatus::AWAITING_VERIFICATION)
                        <form method="POST" action="{{ route('admin.invoices.reject', $invoice->id) }}" onsubmit="return confirm('Reject this payment?')">
                            @csrf @method('PATCH')
                            <button class="flex items-center gap-2 px-5 py-3 rounded-lg bg-error-container/40 text-error border border-error/30 font-bold text-sm">
                                <span class="material-symbols-outlined text-lg">cancel</span> Reject Payment
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
