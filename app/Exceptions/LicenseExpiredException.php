<?php

namespace App\Exceptions;

/** License expired → HTTP 402 (mirrors the Spring backend). */
class LicenseExpiredException extends AppException
{
    public function httpStatus(): int
    {
        return 402;
    }
}
