<?php

namespace App\Exceptions;

class ExistingException extends AppException
{
    public function httpStatus(): int
    {
        return 409;
    }
}
