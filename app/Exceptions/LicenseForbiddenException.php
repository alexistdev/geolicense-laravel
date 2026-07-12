<?php

namespace App\Exceptions;

/** Invalid / mismatched license token → HTTP 403. */
class LicenseForbiddenException extends AppException
{
    public function httpStatus(): int
    {
        return 403;
    }
}
