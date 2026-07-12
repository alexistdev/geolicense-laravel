<?php

namespace App\Enums;

/**
 * Ports com.alexistdev.geolicense.models.entity.OrderStatus (state machine).
 */
enum OrderStatus: string
{
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    /** @return array<int, self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::COMPLETED, self::CANCELLED],
            self::COMPLETED, self::CANCELLED => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
