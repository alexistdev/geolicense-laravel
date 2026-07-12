<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * glo_payment_gateway_configs — ports entity.PaymentGatewayConfig.
 */
class PaymentGatewayConfig extends BaseModel
{
    protected $table = 'glo_payment_gateway_configs';

    protected $fillable = [
        'payment_method_id',
        'api_key',
        'webhook_token',
        'extra_config',
        'created_by',
        'modified_by',
    ];

    protected $hidden = [
        'api_key',
        'webhook_token',
    ];

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
