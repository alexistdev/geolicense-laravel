<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\LicenseService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function __construct(private readonly LicenseService $licenseService) {}

    public function index(Request $request)
    {
        $licenses = $this->licenseService
            ->getAllLicensesByUserId($request->user()->id)
            ->withQueryString();

        return view('user.licenses.index', compact('licenses'));
    }

    public function show(Request $request, string $license)
    {
        $license = $this->licenseService->getLicenseByIdAndUserId($license, $request->user()->id);

        return view('user.licenses.show', compact('license'));
    }
}
