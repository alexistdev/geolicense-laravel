<?php

namespace App\Services;

use App\Enums\LicenseStatus;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Models\License;
use App\Models\LicensePlan;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Ports services.LicenseService — user license reads + license issuance.
 */
class LicenseService
{
    private const SYSTEM_USER = 'System';

    public function getAllLicensesByUserId(string $userId, int $perPage = 10): LengthAwarePaginator
    {
        return License::query()
            ->with(['licensePlan.product', 'licensePlan.licenseType', 'product'])
            ->where('user_id', $userId)
            ->latest('issued_at')
            ->paginate($perPage);
    }

    public function getLicenseByIdAndUserId(string $id, string $userId): License
    {
        $license = License::query()
            ->with(['licensePlan.product', 'licensePlan.licenseType', 'product', 'activations'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (! $license) {
            throw new NotFoundException("License not found: {$id}");
        }

        return $license;
    }

    /**
     * Issue a license for a paid order item (called by InvoiceService::validateInvoice).
     */
    public function addLicense(string $userId, string $licensePlanId, string $orderItemId): License
    {
        $user = User::query()->findOrFail($userId);
        if ($user->is_suspended) {
            throw new NotFoundException("User is suspended: {$userId}");
        }

        $plan = LicensePlan::query()->findOrFail($licensePlanId);
        if (! $plan->is_active) {
            throw new BadRequestException("License plan is not active: {$licensePlanId}");
        }

        $orderItem = OrderItem::query()->find($orderItemId);
        if (! $orderItem) {
            throw new NotFoundException("Order item not found: {$orderItemId}");
        }

        return License::create([
            'user_id' => $user->id,
            'product_id' => $plan->product_id,
            'license_plan_id' => $plan->id,
            'order_item_id' => $orderItem->id,
            'license_key' => self::generateLicenseKey(),
            'max_seats' => $plan->max_seats,
            'used_seats' => 0,
            'issued_at' => now(),
            'expires_at' => now()->addDays($plan->duration_days),
            'status' => LicenseStatus::ACTIVE,
            'created_by' => self::SYSTEM_USER,
            'modified_by' => self::SYSTEM_USER,
        ]);
    }

    public static function generateLicenseKey(): string
    {
        $part = fn () => strtoupper(substr(str_replace('-', '', (string) Str::uuid()), 0, 8));

        return 'GEOLIC-'.$part().'-'.$part();
    }
}
