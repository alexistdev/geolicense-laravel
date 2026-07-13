<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings', [
            'user' => Auth::user(),
            'recaptcha' => [
                'enabled' => filter_var(
                    Setting::get('recaptcha_enabled', config('services.recaptcha.enabled')),
                    FILTER_VALIDATE_BOOL
                ),
                'site_key' => Setting::get('recaptcha_site_key', config('services.recaptcha.site_key')),
                'secret_key' => Setting::get('recaptcha_secret_key', config('services.recaptcha.secret_key')),
            ],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validateWithBag('profile', [
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('glo_users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'modified_by' => $user->email,
        ]);

        return redirect()->route('admin.settings')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => $data['password'],
            'modified_by' => $user->email,
        ]);

        return redirect()->route('admin.settings')->with('success', 'Password updated successfully.');
    }

    public function updateRecaptcha(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('recaptcha', [
            'recaptcha_enabled' => ['nullable', 'boolean'],
            'recaptcha_site_key' => ['nullable', 'string', 'max:255'],
            'recaptcha_secret_key' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::put('recaptcha_enabled', $request->boolean('recaptcha_enabled') ? '1' : '0');
        Setting::put('recaptcha_site_key', $data['recaptcha_site_key'] ?? null);
        Setting::put('recaptcha_secret_key', $data['recaptcha_secret_key'] ?? null);

        return redirect()->route('admin.settings')->with('success', 'reCAPTCHA settings updated successfully.');
    }
}
