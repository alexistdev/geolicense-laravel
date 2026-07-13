<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Models\Invoice;
use App\Models\LicensePlan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Ports services.OrderService — turns a plan selection into Order + OrderItem + Invoice.
 */
class OrderService
{
    private const PREFIX_ORDER = 'ORD';

    private const PREFIX_INVOICE = 'INV';

    public function __construct(private readonly LicenseService $licenseService) {}

    public function createOrder(User $user, string $licensePlanId, int $quantity = 1): Order
    {
        if ($user->is_suspended) {
            throw new NotFoundException("User not found or suspended: {$user->email}");
        }

        $plan = LicensePlan::query()->find($licensePlanId);
        if (! $plan) {
            throw new NotFoundException("License plan not found: {$licensePlanId}");
        }

        if (! $plan->is_active) {
            throw new BadRequestException("License plan is not active: {$licensePlanId}");
        }

        $isFree = $this->isFreePlan($plan);

        if ($isFree) {
            // Free plans are limited to one per product; enforce before charging nothing.
            if ($this->licenseService->userHasFreeLicenseForProduct($user->id, $plan->product_id)) {
                throw new BadRequestException('You already have the free license for this product. Please choose a Premium plan.');
            }
        } elseif ($this->hasPendingInvoice($user->id)) {
            throw new BadRequestException('You already have a pending invoice. Please settle it before ordering again.');
        }

        return DB::transaction(function () use ($user, $plan, $quantity, $isFree) {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $this->generateNumber(self::PREFIX_ORDER),
                'currency' => $plan->currency,
                'status' => $isFree ? OrderStatus::COMPLETED : OrderStatus::PENDING,
            ]);

            $unitPrice = $plan->price;
            $totalPrice = bcmul((string) $unitPrice, (string) $quantity, 4);

            $item = OrderItem::create([
                'order_id' => $order->id,
                'license_plan_id' => $plan->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ]);

            if ($isFree) {
                $this->settleFreeOrder($order, $item, $plan);

                return $order;
            }

            $uniqueCode = random_int(100, 999);
            Invoice::create([
                'order_id' => $order->id,
                'invoice_number' => $this->generateNumber(self::PREFIX_INVOICE),
                'amount' => $item->total_price,
                'currency' => $order->currency,
                'status' => InvoiceStatus::UNPAID,
                'issued_at' => now(),
                'unique_code' => $uniqueCode,
                'total_amount' => bcadd((string) $item->total_price, (string) $uniqueCode, 4),
            ]);

            return $order;
        });
    }

    private function isFreePlan(LicensePlan $plan): bool
    {
        return bccomp((string) $plan->price, '0', 4) === 0;
    }

    /**
     * Zero-price checkout: bill nothing (no unique code), mark the invoice paid,
     * record a system payment and activate the license immediately.
     */
    private function settleFreeOrder(Order $order, OrderItem $item, LicensePlan $plan): void
    {
        Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => $this->generateNumber(self::PREFIX_INVOICE),
            'amount' => $item->total_price,
            'currency' => $order->currency,
            'status' => InvoiceStatus::PAID,
            'issued_at' => now(),
            'unique_code' => 0,
            'total_amount' => $item->total_price,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'FREE',
            'provider_reference' => 'FREE-'.$order->order_number,
            'amount' => $item->total_price,
            'currency' => $order->currency,
            'status' => PaymentStatus::VERIFIED,
            'paid_at' => now(),
        ]);

        $this->licenseService->addLicense($order->user_id, $plan->id, $item->id);
    }

    private function hasPendingInvoice(string $userId): bool
    {
        return Invoice::query()
            ->whereIn('status', [InvoiceStatus::UNPAID->value, InvoiceStatus::AWAITING_VERIFICATION->value])
            ->whereHas('order', fn ($q) => $q->where('user_id', $userId))
            ->exists();
    }

    private function generateNumber(string $prefix): string
    {
        return $prefix.'-'.strtoupper(substr(str_replace('-', '', (string) Str::uuid()), 0, 8));
    }
}
