<x-app-layout title="Invoice {{ $invoice->invoice_number }} — GeoLicense" header="My Account">
    @php
        $payment = $invoice->order?->payment;
        $canPay = $invoice->status->canTransitionTo(\App\Enums\InvoiceStatus::AWAITING_VERIFICATION);
    @endphp
    <div class="p-8 space-y-6 max-w-3xl">
        <a href="{{ route('user.invoice.index') }}" class="inline-flex items-center gap-1 text-on-surface-variant hover:text-primary text-sm">
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

            <div class="py-6 space-y-3">
                @foreach ($invoice->order?->orderItems ?? [] as $item)
                    <div class="flex justify-between items-center p-4 rounded-lg bg-surface-container-high">
                        <div>
                            <p class="text-on-surface font-medium">{{ $item->licensePlan?->name }}</p>
                            <p class="text-on-surface-variant text-xs">{{ $item->licensePlan?->product?->name }} · Qty {{ $item->quantity }}</p>
                        </div>
                        <p class="text-on-surface font-semibold">{{ money($item->total_price, $invoice->currency) }}</p>
                    </div>
                @endforeach
                <div class="flex justify-between pt-3 text-sm text-on-surface-variant">
                    <span>Subtotal</span><span>{{ money($invoice->amount, $invoice->currency) }}</span>
                </div>
                <div class="flex justify-between text-sm text-on-surface-variant">
                    <span>Unique code</span><span>{{ $invoice->unique_code }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-white pt-2 border-t border-white/5">
                    <span>Total</span><span>{{ money($invoice->total_amount, $invoice->currency) }}</span>
                </div>
            </div>

            @if ($payment && $payment->status === \App\Enums\PaymentStatus::PENDING)
                <div class="rounded-lg bg-tertiary/10 border border-tertiary/30 p-4 text-sm text-tertiary flex items-center gap-2">
                    <span class="material-symbols-outlined">hourglass_top</span>
                    Your payment is awaiting admin verification.
                </div>
            @endif

            @if ($canPay)
                <div class="pt-6 border-t border-white/5">
                    <a href="{{ route('user.invoice.payment', $invoice->id) }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-sm">
                        <span class="material-symbols-outlined text-lg">payments</span> Pay this invoice
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
