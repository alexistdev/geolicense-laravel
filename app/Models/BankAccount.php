<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * glo_bank_accounts — ports entity.BankAccount.
 */
class BankAccount extends BaseModel
{
    protected $table = 'glo_bank_accounts';

    protected $fillable = [
        'payment_method_id',
        'bank_name',
        'account_number',
        'account_holder',
        'is_main',
        'is_active',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
