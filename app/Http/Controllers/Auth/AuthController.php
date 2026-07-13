<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Concerns\HandlesRecaptcha;
use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use HandlesRecaptcha;

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect(Auth::user()->role->homeUrl());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ] + $this->recaptchaRules());

        $credentials = ['email' => $request->email, 'password' => $request->password];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        if (Auth::user()->is_suspended) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'This account has been suspended.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        SystemLog::record(
            "{$user->full_name} ({$user->email}) logged in.",
            'INFO',
            ['action' => 'User Login', 'context' => ['user_id' => $user->id]],
        );

        return redirect()->intended($user->role->homeUrl());
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect(Auth::user()->role->homeUrl());
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:glo_users,email'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ] + $this->recaptchaRules());

        $user = User::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => Role::USER,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        SystemLog::record(
            "New account registered: {$user->full_name} ({$user->email}).",
            'INFO',
            ['action' => 'User Registered', 'context' => ['user_id' => $user->id]],
        );

        try {
            $user->notify(new WelcomeNotification($user));
        } catch (\Throwable $e) {
            SystemLog::record(
                "Failed to send welcome email to {$user->email}: {$e->getMessage()}",
                'ERROR',
                ['action' => 'Welcome Email Failed', 'context' => ['user_id' => $user->id]],
            );
        }

        return redirect($user->role->homeUrl());
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user) {
            SystemLog::record(
                "{$user->full_name} ({$user->email}) logged out.",
                'INFO',
                ['action' => 'User Logout', 'user_id' => $user->id, 'causer' => $user->email],
            );
        }

        return redirect()->route('login');
    }
}
