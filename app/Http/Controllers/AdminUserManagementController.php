<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use Illuminate\Http\Request;

class AdminUserManagementController extends Controller
{
    public function index(Request $request)
{
    $query = User::query();

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', "%{$request->search}%")
              ->orWhere('email', 'like', "%{$request->search}%");
        });
    }

    if ($request->filled('role') && $request->role !== 'all') {
        $query->where('role', $request->role);
    }

    $users = $query->latest()->paginate(10);

    $viewData = [
        'users' => $users,
        'totalUsers' => User::count(),
        'admins' => User::where('role', 'admin')->count(),
        'staff' => User::where('role', 'bhw')->count(),
        'citizen' => User::where('role', 'citizen')->count(),
    ];

    if ($request->is('superadmin*')) {
        return view('superadmin.users', $viewData);
    }

    return view('admin.admin_users', $viewData);
}

    public function show($id)
    {
        return view('admin.user-view', [
            'user' => User::findOrFail($id)
        ]);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return back()->with('success', 'User deleted successfully.');
    }


    public function create()
{
    return view('admin.user_create');
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        'role' => 'required'
    ]);

    // Define the uniform default temporary password for all new users
    $defaultPassword = 'TemporaryPassword123!';

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($defaultPassword),
        'role' => $request->role,
        'must_change_password' => true,
    ]);

    return redirect()->route(auth()->user()->isSuperAdmin() ? 'superadmin.users' : 'admin.users')->with('success', 'User created successfully. Default temporary password is: ' . $defaultPassword);
}

public function unlockAccount($id)
{
    $user = \App\Models\User::findOrFail($id);

    $user->update([
        'failed_login_attempts' => 0,
        'is_locked' => false,
    ]);

    return back()->with('success', "User {$user->name}'s account has been unlocked successfully.");
}
}