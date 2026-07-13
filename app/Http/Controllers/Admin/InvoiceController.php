<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\SystemLog;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $keyword = $request->query('keyword');

        $invoices = $keyword
            ? $this->invoiceService->searchInvoices($keyword)->withQueryString()
            : $this->invoiceService->getAllInvoices()->withQueryString();

        return view('admin.invoices.index', compact('invoices', 'keyword'));
    }

    public function show(string $invoice)
    {
        $invoice = $this->invoiceService->getInvoiceDetailForAdmin($invoice);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function validateInvoice(string $invoice): RedirectResponse
    {
        $this->invoiceService->validateInvoice($invoice);

        SystemLog::record(
            "Invoice {$this->invoiceNumber($invoice)} payment was validated — license(s) issued.",
            'INFO',
            ['action' => 'Invoice Validated', 'context' => ['invoice_id' => $invoice]],
        );

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Payment approved. License(s) issued to the customer.');
    }

    public function reject(string $invoice): RedirectResponse
    {
        $this->invoiceService->rejectPayment($invoice);

        SystemLog::record(
            "Invoice {$this->invoiceNumber($invoice)} payment was rejected.",
            'WARNING',
            ['action' => 'Invoice Rejected', 'context' => ['invoice_id' => $invoice]],
        );

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Payment rejected. The invoice is unpaid again.');
    }

    public function void(string $invoice): RedirectResponse
    {
        $this->invoiceService->voidInvoice($invoice);

        SystemLog::record(
            "Invoice {$this->invoiceNumber($invoice)} was voided — order cancelled.",
            'WARNING',
            ['action' => 'Invoice Voided', 'context' => ['invoice_id' => $invoice]],
        );

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice voided. The order has been cancelled.');
    }

    /** Resolve an invoice number for the log message, falling back to the id. */
    private function invoiceNumber(string $invoiceId): string
    {
        return Invoice::whereKey($invoiceId)->value('invoice_number') ?? $invoiceId;
    }
}
