<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * glo_order_items — ports entity.OrderItem.
 */
class OrderItem extends BaseModel
{
    protected $table = 'glo_order_items';

    protected $fillable = [
        'order_id',
        'license_plan_id',
        'quantity',
        'unit_price',
        'total_price',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:4',
            'total_price' => 'decimal:4',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function licensePlan(): BelongsTo
    {
        return $this->belongsTo(LicensePlan::class, 'license_plan_id');
    }
}
