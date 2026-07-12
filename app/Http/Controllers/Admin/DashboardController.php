<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\LicenseStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'active_licenses' => License::where('status', LicenseStatus::ACTIVE->value)->count(),
            'total_revenue' => (float) Invoice::where('status', InvoiceStatus::PAID->value)->sum('total_amount'),
            'pending_clearances' => Invoice::where('status', InvoiceStatus::AWAITING_VERIFICATION->value)->count(),
            'total_users' => User::count(),
            'total_products' => Product::count(),
        ];

        $recentLicenses = License::query()
            ->with(['user', 'licensePlan'])
            ->latest('issued_at')
            ->limit(5)
            ->get();

        $pendingInvoices = Invoice::query()
            ->with('order.user')
            ->where('status', InvoiceStatus::AWAITING_VERIFICATION->value)
            ->latest('issued_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentLicenses', 'pendingInvoices'));
    }
}
