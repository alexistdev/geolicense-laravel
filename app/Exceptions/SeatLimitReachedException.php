<?php

namespace App\Exceptions;

/** Seat limit exceeded → HTTP 429. */
class SeatLimitReachedException extends AppException
{
    public function httpStatus(): int
    {
        return 429;
    }
}
