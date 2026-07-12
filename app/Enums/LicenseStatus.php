<?php

namespace App\Enums;

/**
 * Ports com.alexistdev.geolicense.models.entity.LicenseStatus (state machine).
 */
enum LicenseStatus: string
{
    case ACTIVE = 'ACTIVE';
    case SUSPENDED = 'SUSPENDED';
    case REVOKED = 'REVOKED';

    /** @return array<int, self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::ACTIVE => [self::SUSPENDED, self::REVOKED],
            self::SUSPENDED => [self::ACTIVE, self::REVOKED],
            self::REVOKED => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
