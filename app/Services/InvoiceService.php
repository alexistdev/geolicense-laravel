<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Ports services.InvoiceService — invoice reads plus the payment lifecycle
 * (submit → await verification → validate/reject → issue license).
 */
class InvoiceService
{
    public function __construct(private readonly LicenseService $licenseService) {}

    public function getAllInvoices(int $perPage = 10): LengthAwarePaginator
    {
        return Invoice::query()
            ->with('order.user')
            ->latest('issued_at')
            ->paginate($perPage);
    }

    public function searchInvoices(?string $keyword, int $perPage = 10): LengthAwarePaginator
    {
        return Invoice::query()
            ->with('order.user')
            ->when($keyword, fn ($q) => $q->where('invoice_number', 'like', '%'.$keyword.'%'))
            ->latest('issued_at')
            ->paginate($perPage);
    }

    public function getMyInvoices(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Invoice::query()
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->latest('issued_at')
            ->paginate($perPage);
    }

    public function getInvoiceDetailForAdmin(string $invoiceId): Invoice
    {
        $invoice = Invoice::query()
            ->with(['order.user', 'order.orderItems.licensePlan.product', 'order.payment.bankAccount'])
            ->find($invoiceId);

        if (! $invoice) {
            throw new NotFoundException("Invoice not found: {$invoiceId}");
        }

        return $invoice;
    }

    public function getInvoiceDetailForUser(string $invoiceId, User $user): Invoice
    {
        $invoice = Invoice::query()
            ->with(['order.orderItems.licensePlan.product', 'order.payment.bankAccount'])
            ->where('id', $invoiceId)
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->first();

        if (! $invoice) {
            throw new NotFoundException("Invoice not found: {$invoiceId}");
        }

        return $invoice;
    }

    public function submitPayment(string $invoiceId, User $user, string $provider, string $providerReference): void
    {
        DB::transaction(function () use ($invoiceId, $user, $provider, $providerReference) {
            $invoice = Invoice::query()
                ->where('id', $invoiceId)
                ->whereHas('order', fn ($q) => $q->where('user_id', $user->id))
                ->first();

            if (! $invoice) {
                throw new NotFoundException("Invoice not found: {$invoiceId}");
            }

            if (! $invoice->status->canTransitionTo(InvoiceStatus::AWAITING_VERIFICATION)) {
                throw new BadRequestException("Invoice is not in a payable state: {$invoiceId}");
            }

            $order = $invoice->order;
            $payment = $order->payment;

            if ($payment) {
                if (! $payment->status->canTransitionTo(PaymentStatus::PENDING)) {
                    throw new BadRequestException("Payment has already been submitted: {$invoiceId}");
                }
            } else {
                $payment = new Payment([
                    'order_id' => $order->id,
                    'amount' => $invoice->total_amount,
                    'currency' => $invoice->currency,
                ]);
            }

            $payment->provider = $provider;
            $payment->provider_reference = $providerReference;
            $payment->status = PaymentStatus::PENDING;
            $payment->paid_at = now();
            $payment->save();

            $invoice->status = InvoiceStatus::AWAITING_VERIFICATION;
            $invoice->save();
        });
    }

    public function validateInvoice(string $invoiceId): void
    {
        DB::transaction(function () use ($invoiceId) {
            $invoice = Invoice::query()->with('order.orderItems')->find($invoiceId);
            if (! $invoice) {
                throw new NotFoundException("Invoice not found: {$invoiceId}");
            }

            if (! $invoice->status->canTransitionTo(InvoiceStatus::PAID)) {
                throw new BadRequestException("Invoice has already been validated: {$invoiceId}");
            }

            $order = $invoice->order;
            $payment = $order->payment;
            if (! $payment) {
                throw new NotFoundException("Payment not found for order: {$order->id}");
            }

            $payment->status = PaymentStatus::VERIFIED;
            $payment->save();

            $order->status = OrderStatus::COMPLETED;
            $order->save();

            $invoice->status = InvoiceStatus::PAID;
            $invoice->save();

            foreach ($order->orderItems as $item) {
                $this->licenseService->addLicense($order->user_id, $item->license_plan_id, $item->id);
            }
        });
    }

    public function rejectPayment(string $invoiceId): void
    {
        DB::transaction(function () use ($invoiceId) {
            $invoice = Invoice::query()->with('order.payment')->find($invoiceId);
            if (! $invoice) {
                throw new NotFoundException("Invoice not found: {$invoiceId}");
            }

            if (! $invoice->status->canTransitionTo(InvoiceStatus::UNPAID)) {
                throw new BadRequestException("Invoice is not awaiting verification: {$invoiceId}");
            }

            $payment = $invoice->order->payment;
            if (! $payment) {
                throw new NotFoundException("Payment not found for order: {$invoice->order->id}");
            }

            if (! $payment->status->canTransitionTo(PaymentStatus::REJECTED)) {
                throw new BadRequestException("Payment is not pending: {$invoiceId}");
            }

            $payment->status = PaymentStatus::REJECTED;
            $payment->save();

            $invoice->status = InvoiceStatus::UNPAID;
            $invoice->save();
        });
    }

    public function voidInvoice(string $invoiceId): void
    {
        DB::transaction(function () use ($invoiceId) {
            $invoice = Invoice::query()->with('order')->find($invoiceId);
            if (! $invoice) {
                throw new NotFoundException("Invoice not found: {$invoiceId}");
            }

            if (! $invoice->status->canTransitionTo(InvoiceStatus::CANCELLED)) {
                throw new BadRequestException("Invoice cannot be voided in its current state: {$invoiceId}");
            }

            $order = $invoice->order;
            if ($order && $order->status->canTransitionTo(OrderStatus::CANCELLED)) {
                $order->status = OrderStatus::CANCELLED;
                $order->save();
            }

            $invoice->status = InvoiceStatus::CANCELLED;
            $invoice->save();
        });
    }
}
