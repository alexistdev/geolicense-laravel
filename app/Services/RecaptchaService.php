<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Wraps Google reCAPTCHA v3 configuration and verification. Configuration is
 * read from the glo_settings store (admin-editable) and falls back to the
 * services.recaptcha config / environment defaults.
 */
class RecaptchaService
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /** Memoised per instance so a single request hits the DB at most once. */
    private ?array $config = null;

    private function config(): array
    {
        return $this->config ??= [
            'enabled' => filter_var(
                Setting::get('recaptcha_enabled', config('services.recaptcha.enabled')),
                FILTER_VALIDATE_BOOL
            ),
            'site_key' => Setting::get('recaptcha_site_key', config('services.recaptcha.site_key')),
            'secret_key' => Setting::get('recaptcha_secret_key', config('services.recaptcha.secret_key')),
        ];
    }

    /**
     * reCAPTCHA is only active when the toggle is on AND both keys are present,
     * so we never render a broken widget or block logins on a half-config.
     */
    public function enabled(): bool
    {
        $config = $this->config();

        return $config['enabled'] && filled($config['site_key']) && filled($config['secret_key']);
    }

    public function siteKey(): ?string
    {
        return $this->config()['site_key'];
    }

    public function secretKey(): ?string
    {
        return $this->config()['secret_key'];
    }

    /**
     * Minimum v3 score (0.0–1.0) required to treat a request as human.
     */
    public function scoreThreshold(): float
    {
        return (float) config('services.recaptcha.score_threshold', 0.5);
    }

    /**
     * Verify a client token against Google's siteverify endpoint. For v3 the
     * response carries a score, which must clear the configured threshold.
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(10)->post(self::VERIFY_URL, [
                'secret' => $this->secretKey(),
                'response' => $token,
                'remoteip' => $ip,
            ]);
        } catch (Throwable) {
            return false;
        }

        if (! $response->successful() || $response->json('success') !== true) {
            return false;
        }

        // v3 always returns a 0.0–1.0 score; reject anything below the threshold.
        // (A missing score means a v2 key, which we let through unchanged.)
        $score = $response->json('score');

        return $score === null || (float) $score >= $this->scoreThreshold();
    }
}
