<?php

use App\Services\RecaptchaService;

if (! function_exists('recaptcha')) {
    /**
     * Resolve the reCAPTCHA service — used by views, rules and controllers to
     * check whether the feature is enabled and to verify tokens.
     */
    function recaptcha(): RecaptchaService
    {
        return app(RecaptchaService::class);
    }
}

if (! function_exists('money')) {
    /**
     * Format an amount for display. IDR uses no decimals and dot grouping
     * (Rp1.000.000); other currencies fall back to a generic 2-decimal format.
     */
    function money(int|float|string|null $amount, string $currency = 'IDR'): string
    {
        $value = (float) ($amount ?? 0);

        if (strtoupper($currency) === 'IDR') {
            return 'Rp'.number_format($value, 0, ',', '.');
        }

        return strtoupper($currency).' '.number_format($value, 2, '.', ',');
    }
}
