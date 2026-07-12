<x-app-layout title="Invoices — GeoLicense" header="Admin Console">
    <div class="p-8 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight">Billing &amp; Invoices</h1>
                <p class="text-on-surface-variant text-sm mt-1">Review payments and approve or reject verification requests.</p>
            </div>
            <form method="GET" class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-lg">search</span>
                <input name="keyword" value="{{ $keyword }}" placeholder="Search invoice number…"
                    class="bg-surface-container border-none rounded-lg py-2.5 pl-10 pr-4 text-sm w-full md:w-72 text-on-surface placeholder:text-on-surface-variant/50 focus:ring-1 focus:ring-primary outline-none">
            </form>
        </div>

        <div class="bg-surface-container rounded-2xl p-6 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-on-surface-variant text-[0.6875rem] uppercase tracking-widest border-b border-white/5">
                        <th class="py-3">Invoice</th>
                        <th class="py-3">Customer</th>
                        <th class="py-3">Amount</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Issued</th>
                        <th class="py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-white/5">
                            <td class="py-3.5 font-mono text-xs text-primary">{{ $invoice->invoice_number }}</td>
                            <td class="py-3.5 text-on-surface">{{ $invoice->order?->user?->full_name ?? '—' }}</td>
                            <td class="py-3.5 text-on-surface font-semibold">{{ money($invoice->total_amount, $invoice->currency) }}</td>
                            <td class="py-3.5"><x-status-badge :status="$invoice->status" /></td>
                            <td class="py-3.5 text-on-surface-variant text-xs">{{ $invoice->issued_at?->format('d M Y') }}</td>
                            <td class="py-3.5 text-right">
                                <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="inline-flex items-center gap-1 text-primary hover:text-primary-container text-sm font-medium">
                                    Review <span class="material-symbols-outlined text-base">chevron_right</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-on-surface-variant">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @include('partials.pagination', ['paginator' => $invoices])
        </div>
    </div>
</x-app-layout>
