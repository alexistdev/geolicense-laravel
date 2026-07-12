<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * glo_invoices — ports entity.Invoice.
 */
class Invoice extends BaseModel
{
    protected $table = 'glo_invoices';

    protected $fillable = [
        'order_id',
        'invoice_number',
        'amount',
        'currency',
        'status',
        'issued_at',
        'unique_code',
        'total_amount',
        'discount',
        'tax',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'total_amount' => 'decimal:4',
            'discount' => 'decimal:4',
            'tax' => 'decimal:4',
            'unique_code' => 'integer',
            'status' => InvoiceStatus::class,
            'issued_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
