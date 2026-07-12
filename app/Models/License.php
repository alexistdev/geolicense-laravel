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
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function licensePlan(): BelongsTo
    {
        return $this->belongsTo(LicensePlan::class, 'license_plan_id');
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
