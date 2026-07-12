<x-app-layout title="Invoice {{ $invoice->invoice_number }} — GeoLicense" header="Admin Console">
    @php $payment = $invoice->order?->payment; @endphp
    <div class="p-8 space-y-6 max-w-4xl" x-data="{ approveOpen: false, voidOpen: false }">
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
            @if ($invoice->status->canTransitionTo(\App\Enums\InvoiceStatus::PAID) || $invoice->status->canTransitionTo(\App\Enums\InvoiceStatus::UNPAID) || $invoice->status->canTransitionTo(\App\Enums\InvoiceStatus::CANCELLED))
                <div class="border-t border-white/5 pt-6 flex flex-wrap gap-3">
                    @if ($invoice->status->canTransitionTo(\App\Enums\InvoiceStatus::PAID))
                        <button type="button" @click="approveOpen = true"
                            class="flex items-center gap-2 px-5 py-3 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-sm">
                            <span class="material-symbols-outlined text-lg">check_circle</span> Approve &amp; Issue License
                        </button>
                    @endif
                    @if ($invoice->status === \App\Enums\InvoiceStatus::AWAITING_VERIFICATION)
                        <form method="POST" action="{{ route('admin.invoices.reject', $invoice->id) }}" onsubmit="return confirm('Reject this payment?')">
                            @csrf @method('PATCH')
                            <button class="flex items-center gap-2 px-5 py-3 rounded-lg bg-error-container/40 text-error border border-error/30 font-bold text-sm">
                                <span class="material-symbols-outlined text-lg">cancel</span> Reject Payment
                            </button>
                        </form>
                    @endif
                    @if ($invoice->status->canTransitionTo(\App\Enums\InvoiceStatus::CANCELLED))
                        <button type="button" @click="voidOpen = true"
                            class="flex items-center gap-2 px-5 py-3 rounded-lg bg-surface-container-high text-on-surface-variant border border-white/10 font-bold text-sm hover:text-error hover:border-error/30">
                            <span class="material-symbols-outlined text-lg">block</span> Void Invoice
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Approve confirmation modal --}}
        @if ($invoice->status->canTransitionTo(\App\Enums\InvoiceStatus::PAID))
            <div x-show="approveOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display:none">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="approveOpen = false"></div>
                <div class="relative w-full max-w-md bg-surface-container rounded-2xl border border-white/10 shadow-2xl p-6"
                     @keydown.escape.window="approveOpen = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-11 h-11 rounded-full bg-primary-container/30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">check_circle</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white">Approve this payment?</h3>
                            <p class="text-sm text-on-surface-variant mt-1.5">
                                Invoice <span class="font-mono text-primary">{{ $invoice->invoice_number }}</span> will be marked
                                <span class="font-semibold text-on-surface">Paid</span> and the license(s) will be issued to
                                <span class="font-semibold text-on-surface">{{ $invoice->order?->user?->full_name }}</span>.
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="approveOpen = false"
                            class="px-4 py-2.5 rounded-lg bg-surface-container-high text-on-surface text-sm font-medium">
                            Cancel
                        </button>
                        <form method="POST" action="{{ route('admin.invoices.validate', $invoice->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="flex items-center gap-2 px-5 py-2.5 rounded-lg bg-gradient-to-r from-primary to-primary-container text-on-primary font-bold text-sm">
                                <span class="material-symbols-outlined text-lg">check_circle</span> Approve &amp; Issue
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Void confirmation modal --}}
        @if ($invoice->status->canTransitionTo(\App\Enums\InvoiceStatus::CANCELLED))
            <div x-show="voidOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display:none">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="voidOpen = false"></div>
                <div class="relative w-full max-w-md bg-surface-container rounded-2xl border border-white/10 shadow-2xl p-6"
                     @keydown.escape.window="voidOpen = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-11 h-11 rounded-full bg-error-container/30 flex items-center justify-center">
                            <span class="material-symbols-outlined text-error">block</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white">Void this invoice?</h3>
                            <p class="text-sm text-on-surface-variant mt-1.5">
                                Invoice <span class="font-mono text-primary">{{ $invoice->invoice_number }}</span> will be marked
                                <span class="font-semibold text-on-surface">Cancelled</span> and its order will be cancelled. This cannot be undone.
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="voidOpen = false"
                            class="px-4 py-2.5 rounded-lg bg-surface-container-high text-on-surface text-sm font-medium">
                            Cancel
                        </button>
                        <form method="POST" action="{{ route('admin.invoices.void', $invoice->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="flex items-center gap-2 px-5 py-2.5 rounded-lg bg-error text-on-error font-bold text-sm">
                                <span class="material-symbols-outlined text-lg">block</span> Void Invoice
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
