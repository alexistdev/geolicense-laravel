<?php

namespace App\Services;

use App\Enums\LicenseStatus;
use App\Exceptions\LicenseExpiredException;
use App\Exceptions\LicenseForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\SeatLimitReachedException;
use App\Models\License;
use App\Models\LicenseActivation;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Ports services.LicenseTokenService — issues/validates the short-lived HMAC-SHA256
 * license activation token, enforcing seat limits, expiry and per-machine activation.
 */
class LicenseTokenService
{
    private const SYSTEM_USER = 'System';

    /**
     * Activate a license on a machine and return a signed token.
     *
     * @return array<string, mixed>
     */
    public function activate(string $licenseKey, string $machineId, ?string $osInfo): array
    {
        return DB::transaction(function () use ($licenseKey, $machineId, $osInfo) {
            $license = License::query()->where('license_key', $licenseKey)->first();
            if (! $license) {
                throw new NotFoundException("License not found: {$licenseKey}");
            }

            if ($license->status !== LicenseStatus::ACTIVE) {
                throw new LicenseForbiddenException("License is not active: {$licenseKey}");
            }

            if ($license->expires_at->isPast()) {
                throw new LicenseExpiredException("License has expired: {$licenseKey}");
            }

            $activation = LicenseActivation::query()
                ->where('license_id', $license->id)
                ->where('machine_id', $machineId)
                ->first();

            if (! $activation) {
                if ($license->used_seats >= $license->max_seats) {
                    throw new SeatLimitReachedException("Seat limit reached for license: {$licenseKey}");
                }

                LicenseActivation::create([
                    'license_id' => $license->id,
                    'machine_id' => $machineId,
                    'os_info' => $osInfo,
                    'activated_at' => now(),
                    'last_verified_at' => now(),
                    'is_activated' => true,
                    'created_by' => self::SYSTEM_USER,
                    'modified_by' => self::SYSTEM_USER,
                ]);

                $license->used_seats = $license->used_seats + 1;
                $license->modified_by = self::SYSTEM_USER;
                $license->save();
            } else {
                $activation->last_verified_at = now();
                $activation->modified_by = self::SYSTEM_USER;
                $activation->save();
            }

            $expirationMs = (int) config('geolicense.token.expiration');
            $tokenExpiresAt = now()->addSeconds(intdiv($expirationMs, 1000));
            if ($tokenExpiresAt->greaterThan($license->expires_at)) {
                $tokenExpiresAt = $license->expires_at->copy();
            }

            $token = $this->generateToken($license->license_key, $machineId, $tokenExpiresAt);

            return [
                'valid' => true,
                'licenseKey' => $license->license_key,
                'machineId' => $machineId,
                'token' => $token,
                'usedSeats' => $license->used_seats,
                'maxSeats' => $license->max_seats,
                'licenseExpiresAt' => $license->expires_at->toIso8601String(),
                'tokenExpiresAt' => $tokenExpiresAt->toIso8601String(),
            ];
        });
    }

    /**
     * Validate a license token against the DB activation record.
     *
     * @return array<string, mixed>
     */
    public function verify(string $token, string $machineId): array
    {
        try {
            $claims = (array) JWT::decode($token, new Key($this->signingKey(), 'HS256'));
        } catch (Throwable $e) {
            throw new LicenseForbiddenException('Invalid license token: '.$e->getMessage());
        }

        $licenseKey = $claims['licenseKey'] ?? null;
        $tokenMachineId = $claims['machineId'] ?? null;

        if (! $licenseKey || ! $tokenMachineId) {
            throw new LicenseForbiddenException('License token is missing required claims');
        }

        if ($tokenMachineId !== $machineId) {
            throw new LicenseForbiddenException("License token does not match machine: {$machineId}");
        }

        $license = License::query()->where('license_key', $licenseKey)->first();
        if (! $license) {
            throw new NotFoundException("License not found: {$licenseKey}");
        }

        if ($license->status !== LicenseStatus::ACTIVE) {
            throw new LicenseForbiddenException("License is not active: {$licenseKey}");
        }

        if ($license->expires_at->isPast()) {
            throw new LicenseExpiredException("License has expired: {$licenseKey}");
        }

        $activation = LicenseActivation::query()
            ->where('license_id', $license->id)
            ->where('machine_id', $machineId)
            ->first();

        if (! $activation) {
            throw new LicenseForbiddenException("No activation found for machine: {$machineId}");
        }

        if (! $activation->is_activated) {
            throw new LicenseForbiddenException("Activation is disabled for machine: {$machineId}");
        }

        $activation->last_verified_at = now();
        $activation->modified_by = self::SYSTEM_USER;
        $activation->save();

        $tokenExpiresAt = isset($claims['exp']) ? Carbon::createFromTimestamp($claims['exp']) : null;

        return [
            'valid' => true,
            'licenseKey' => $licenseKey,
            'machineId' => $machineId,
            'status' => $license->status->value,
            'licenseExpiresAt' => $license->expires_at->toIso8601String(),
            'tokenExpiresAt' => $tokenExpiresAt?->toIso8601String(),
            'lastVerifiedAt' => $activation->last_verified_at->toIso8601String(),
        ];
    }

    private function generateToken(string $licenseKey, string $machineId, Carbon $expiresAt): string
    {
        $payload = [
            'licenseKey' => $licenseKey,
            'machineId' => $machineId,
            'sub' => $licenseKey,
            'iat' => now()->timestamp,
            'exp' => $expiresAt->timestamp,
        ];

        return JWT::encode($payload, $this->signingKey(), 'HS256');
    }

    /**
     * The HMAC key — base64-decoded from the configured secret, matching the
     * Spring server's Keys.hmacShaKeyFor(Decoders.BASE64.decode(secret)).
     */
    private function signingKey(): string
    {
        return base64_decode(config('geolicense.token.secret'));
    }
}
