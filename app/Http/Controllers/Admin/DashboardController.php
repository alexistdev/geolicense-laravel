<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\LicenseStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /** A session counts as "online" if it was active within this many minutes. */
    private const ONLINE_WINDOW_MINUTES = 5;

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

        $onlineUsers = $this->onlineUsers();

        return view('admin.dashboard', compact('stats', 'recentLicenses', 'pendingInvoices', 'onlineUsers'));
    }

    /**
     * Non-admin users with a database session active in the last few minutes.
     *
     * @return \Illuminate\Support\Collection<int, array{email: string, last_activity: Carbon}>
     */
    private function onlineUsers()
    {
        $threshold = now()->subMinutes(self::ONLINE_WINDOW_MINUTES)->getTimestamp();

        // Most-recent activity per authenticated session (a user may have several).
        $lastActivityByUser = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $threshold)
            ->selectRaw('user_id, MAX(last_activity) as last_activity')
            ->groupBy('user_id')
            ->pluck('last_activity', 'user_id');

        if ($lastActivityByUser->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $lastActivityByUser->keys())
            ->where('role', '!=', Role::ADMIN->value)
            ->orderBy('full_name')
            ->get()
            ->map(fn (User $user) => [
                'email' => $user->email,
                'last_activity' => Carbon::createFromTimestamp($lastActivityByUser[$user->id]),
            ])
            ->values();
    }
}
