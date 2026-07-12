<?php

namespace App\Http\Controllers\User;

use App\Enums\InvoiceStatus;
use App\Enums\LicenseStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\License;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'active_licenses' => License::where('user_id', $user->id)
                ->where('status', LicenseStatus::ACTIVE->value)->count(),
            'total_licenses' => License::where('user_id', $user->id)->count(),
            'pending_invoices' => Invoice::whereHas('order', fn ($q) => $q->where('user_id', $user->id))
                ->whereIn('status', [InvoiceStatus::UNPAID->value, InvoiceStatus::AWAITING_VERIFICATION->value])
                ->count(),
            'paid_invoices' => Invoice::whereHas('order', fn ($q) => $q->where('user_id', $user->id))
                ->where('status', InvoiceStatus::PAID->value)->count(),
        ];

        $recentLicenses = License::query()
            ->with(['licensePlan.product', 'product'])
            ->where('user_id', $user->id)
            ->latest('issued_at')
            ->limit(5)
            ->get();

        return view('user.dashboard', compact('stats', 'recentLicenses'));
    }
}
