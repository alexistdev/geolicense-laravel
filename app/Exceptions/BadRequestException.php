<?php

namespace App\Exceptions;

class BadRequestException extends AppException
{
    public function httpStatus(): int
    {
        return 400;
    }
}
