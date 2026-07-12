<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Base for domain exceptions. Each subclass maps to an HTTP status, mirroring
 * the Spring GlobalExceptionHandler. Rendering is wired in bootstrap/app.php.
 */
abstract class AppException extends RuntimeException
{
    abstract public function httpStatus(): int;
}
