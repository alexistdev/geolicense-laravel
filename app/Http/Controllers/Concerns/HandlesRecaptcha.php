<?php

namespace App\Http\Controllers\Concerns;

use App\Rules\Recaptcha;

trait HandlesRecaptcha
{
    /**
     * Validation rules for the reCAPTCHA field — empty when the feature is
     * disabled so forms keep working with no widget rendered.
     *
     * @return array<string, mixed>
     */
    protected function recaptchaRules(): array
    {
        return recaptcha()->enabled()
            ? ['g-recaptcha-response' => ['required', new Recaptcha]]
            : [];
    }
}
