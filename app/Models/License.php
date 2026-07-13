<?php

namespace App\Models;

use App\Enums\LicenseStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * glo_licenses — ports entity.License.
 */
class License extends BaseModel
{
    protected $table = 'glo_licenses';

    protected $fillable = [
        'user_id',
        'product_id',
        'license_plan_id',
        'order_item_id',
        'license_key',
        'max_seats',
        'used_seats',
        'issued_at',
        'expires_at',
        'status',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'max_seats' => 'integer',
            'used_seats' => 'integer',
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'status' => LicenseStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        // A license is permanently tied to the product it was issued for,
        // so keep resolving it even after the product is soft-deleted.
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }

    public function licensePlan(): BelongsTo
    {
        // Likewise the plan: a soft-deleted plan must still surface its name
        // on existing licenses (e.g. the license detail title).
        return $this->belongsTo(LicensePlan::class, 'license_plan_id')->withTrashed();
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function activations(): HasMany
    {
        return $this->hasMany(LicenseActivation::class, 'license_id');
    }
}
