<?php

namespace App\Exceptions;

class NotFoundException extends AppException
{
    public function httpStatus(): int
    {
        return 404;
    }
}
