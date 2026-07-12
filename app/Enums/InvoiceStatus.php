<?php

namespace App\Enums;

/**
 * Ports com.alexistdev.geolicense.models.entity.InvoiceStatus (state machine).
 */
enum InvoiceStatus: string
{
    case UNPAID = 'UNPAID';
    case AWAITING_VERIFICATION = 'AWAITING_VERIFICATION';
    case PAID = 'PAID';
    case CANCELLED = 'CANCELLED';

    /** @return array<int, self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::UNPAID => [self::AWAITING_VERIFICATION, self::PAID, self::CANCELLED],
            self::AWAITING_VERIFICATION => [self::PAID, self::UNPAID],
            self::PAID, self::CANCELLED => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Unpaid',
            self::AWAITING_VERIFICATION => 'Awaiting Verification',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
        };
    }
}
