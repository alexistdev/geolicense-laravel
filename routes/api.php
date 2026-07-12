<?php

use App\Http\Controllers\Api\LicenseApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public license API (client-facing, stateless)
|--------------------------------------------------------------------------
| Mirrors the Spring endpoints:
|   POST /api/v1/licenses/activate
|   POST /api/v1/licenses/verify
*/
Route::prefix('v1')->group(function () {
    Route::post('/licenses/activate', [LicenseApiController::class, 'activate']);
    Route::post('/licenses/verify', [LicenseApiController::class, 'verify']);
});
