<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates the `g-recaptcha-response` token against Google. Only attach this
 * rule when reCAPTCHA is enabled (see HandlesRecaptcha::recaptchaRules()).
 */
class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! recaptcha()->verify(is_string($value) ? $value : null, request()->ip())) {
            $fail('reCAPTCHA verification failed. Please try again.');
        }
    }
}
