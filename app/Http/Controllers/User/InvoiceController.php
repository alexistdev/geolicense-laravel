<?php

namespace App\Http\Controllers\User;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $invoices = $this->invoiceService->getMyInvoices($request->user())->withQueryString();

        return view('user.invoices.index', compact('invoices'));
    }

    public function show(Request $request, string $invoice)
    {
        $invoice = $this->invoiceService->getInvoiceDetailForUser($invoice, $request->user());

        return view('user.invoices.show', compact('invoice'));
    }

    public function payment(Request $request, string $invoice)
    {
        $invoice = $this->invoiceService->getInvoiceDetailForUser($invoice, $request->user());

        if (! $invoice->status->canTransitionTo(InvoiceStatus::AWAITING_VERIFICATION)) {
            return redirect()
                ->route('user.invoice.show', $invoice->id)
                ->with('error', 'This invoice can no longer be paid.');
        }

        $bankAccount = BankAccount::query()
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->first();

        return view('user.invoices.payment', compact('invoice', 'bankAccount'));
    }

    public function submitPayment(Request $request, string $invoice): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', 'string', 'max:255'],
            'provider_reference' => ['required', 'string', 'max:255'],
        ]);

        $this->invoiceService->submitPayment(
            $invoice,
            $request->user(),
            $data['provider'],
            $data['provider_reference'],
        );

        return redirect()
            ->route('user.invoice.show', $invoice)
            ->with('success', 'Payment submitted. Awaiting admin verification.');
    }
}
