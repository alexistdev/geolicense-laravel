<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * glo_payments — ports entity.Payment.
 */
class Payment extends BaseModel
{
    protected $table = 'glo_payments';

    protected $fillable = [
        'order_id',
        'payment_method_id',
        'bank_account_id',
        'snapshot_bank_name',
        'snapshot_account_number',
        'snapshot_account_holder',
        'provider',
        'provider_reference',
        'amount',
        'currency',
        'status',
        'paid_at',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }
}
