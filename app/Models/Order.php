<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * glo_orders — ports entity.Orders.
 */
class Order extends BaseModel
{
    protected $table = 'glo_orders';

    protected $fillable = [
        'user_id',
        'order_number',
        'currency',
        'status',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'order_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_id');
    }
}
