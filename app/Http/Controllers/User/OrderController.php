<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'license_plan_id' => ['required', 'uuid', 'exists:glo_license_plans,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $order = $this->orderService->createOrder(
            $request->user(),
            $data['license_plan_id'],
            (int) ($data['quantity'] ?? 1),
        );

        // Free plans are settled instantly (COMPLETED) — the license is already active.
        if ($order->status === OrderStatus::COMPLETED) {
            return redirect()
                ->route('user.license.index')
                ->with('success', 'Your free license is now active.');
        }

        $order->load('invoice');

        return redirect()
            ->route('user.invoice.show', $order->invoice->id)
            ->with('success', 'Order created. Please complete the payment for your invoice.');
    }
}
