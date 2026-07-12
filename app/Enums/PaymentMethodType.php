<?php

namespace App\Enums;

/**
 * Ports com.alexistdev.geolicense.models.entity.PaymentMethodType.
 */
enum PaymentMethodType: string
{
    case BANK_TRANSFER = 'BANK_TRANSFER';
    case XENDIT = 'XENDIT';
    case OTHER = 'OTHER';
}
