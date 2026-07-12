<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * glo_license_plans — ports entity.LicensePlan.
 * Keeps the original snake_case duration_days / max_seats field names.
 */
class LicensePlan extends BaseModel
{
    protected $table = 'glo_license_plans';

    protected $fillable = [
        'product_id',
        'license_type_id',
        'name',
        'billing_cycle',
        'duration_days',
        'max_seats',
        'price',
        'currency',
        'is_active',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'max_seats' => 'integer',
            'price' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function licenseType(): BelongsTo
    {
        return $this->belongsTo(LicenseType::class, 'license_type_id');
    }
}
