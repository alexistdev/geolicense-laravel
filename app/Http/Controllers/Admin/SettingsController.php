<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings', ['user' => Auth::user()]);
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
}
