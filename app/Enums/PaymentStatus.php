<?php

namespace App\Enums;

/**
 * Ports com.alexistdev.geolicense.models.entity.PaymentStatus (state machine).
 */
enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case VERIFIED = 'VERIFIED';
    case REJECTED = 'REJECTED';

    /** @return array<int, self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::VERIFIED, self::REJECTED],
            self::VERIFIED => [],
            self::REJECTED => [self::PENDING],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
