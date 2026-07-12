<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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
}
