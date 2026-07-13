<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->query('filter');

        $users = User::query()
            ->when($keyword, fn ($q) => $q
                ->where('full_name', 'like', '%'.$keyword.'%')
                ->orWhere('email', 'like', '%'.$keyword.'%'))
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'keyword'));
    }

    public function show(User $user)
    {
        $user->loadCount(['orders', 'licenses']);

        $licenses = $user->licenses()
            ->with('product')
            ->latest('issued_at')
            ->take(5)
            ->get();

        $orders = $user->orders()
            ->withCount('orderItems')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.users.show', compact('user', 'licenses', 'orders'));
    }

    public function suspend(User $user): RedirectResponse
    {
        if ($user->is(Auth::user())) {
            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', 'You cannot suspend your own account.');
        }

        $user->update([
            'is_suspended' => ! $user->is_suspended,
            'modified_by' => Auth::user()?->email,
        ]);

        $message = $user->is_suspended
            ? "{$user->full_name}'s account has been suspended."
            : "{$user->full_name}'s account has been reactivated.";

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', $message);
    }
}
