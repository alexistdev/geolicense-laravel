<?php

namespace App\Models;

use App\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * glo_payment_methods — ports entity.PaymentMethod.
 */
class PaymentMethod extends BaseModel
{
    protected $table = 'glo_payment_methods';

    protected $fillable = [
        'type',
        'display_name',
        'is_active',
        'sort_order',
        'created_by',
        'modified_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentMethodType::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'payment_method_id');
    }

    public function gatewayConfig(): HasOne
    {
        return $this->hasOne(PaymentGatewayConfig::class, 'payment_method_id');
    }
}
