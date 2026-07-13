<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LicenseTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public, stateless license endpoints consumed by client applications.
 * Returns the same {status, messages, payload} envelope as the Spring backend.
 */
class LicenseApiController extends Controller
{
    public function __construct(private readonly LicenseTokenService $tokenService) {}

    public function activate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'licenseKey' => ['required', 'string'],
            'machineId' => ['required', 'string'],
            'productSku' => ['required', 'string'],
            'osInfo' => ['nullable', 'string'],
        ]);

        $payload = $this->tokenService->activate(
            $data['licenseKey'],
            $data['machineId'],
            $data['productSku'],
            $data['osInfo'] ?? null,
        );

        return $this->ok($payload, 'License activated successfully.');
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'machineId' => ['required', 'string'],
            'productSku' => ['required', 'string'],
        ]);

        $payload = $this->tokenService->verify($data['token'], $data['machineId'], $data['productSku']);

        return $this->ok($payload, 'License verified successfully.');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function ok(array $payload, string $message): JsonResponse
    {
        return response()->json([
            'status' => true,
            'messages' => [$message],
            'payload' => $payload,
        ]);
    }
}
