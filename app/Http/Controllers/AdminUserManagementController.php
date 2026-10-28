<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use Illuminate\Http\Request;
use App\Traits\LogsActivity;

class AdminUserManagementController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
{
    $showArchived = $request->boolean('archived');
    $query = $showArchived ? User::onlyTrashed() : User::query();

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', "%{$request->search}%")
              ->orWhere('email', 'like', "%{$request->search}%");
        });
    }

    if ($request->filled('role') && $request->role !== 'all') {
        $query->where('role', $request->role);
    }

    $users = $showArchived
        ? $query->orderByDesc('deleted_at')->paginate(10)->withQueryString()
        : $query->latest()->paginate(10)->withQueryString();

    $viewData = [
        'users' => $users,
        'showArchived' => $showArchived,
        'archivedCount' => User::onlyTrashed()->count(),
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

    // Marking a user inactive archives them: they can no longer sign in until restored.
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ((int) $user->id === (int) auth()->id()) {
            return back()->withErrors(['user' => 'You cannot mark your own account as inactive.']);
        }

        $name = $user->name;
        $user->delete();
        $this->logAdminActivity('user.archived', "Marked user {$name} as inactive and archived the account.", 'governance', $user);

        return back()->with('success', "{$name} was marked inactive and moved to the archive.");
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        $user->restore();
        $user->forceFill([
            'must_change_password' => true,
            'failed_login_attempts' => 0,
            'is_locked' => false,
        ])->save();

        if ($user->citizen_id) {
            \App\Models\citizens::onlyTrashed()->whereKey($user->citizen_id)->restore();
        }

        $this->logAdminActivity('user.restored', "Reactivated user {$user->name}. A password change is required at next login.", 'governance', $user);

        return back()->with('success', "{$user->name} is active again and must change their password at next login.");
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

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($defaultPassword),
        'role' => $request->role,
        'must_change_password' => true,
    ]);

    $this->logAdminActivity('user.created', "Created user {$user->name} with role {$user->role}.", 'governance', $user, null, ['role' => $user->role]);

    return redirect()->route(auth()->user()->isSuperAdmin() ? 'superadmin.users' : 'admin.users')->with('success', 'User created successfully. Default temporary password is: ' . $defaultPassword);
}

public function unlockAccount($id)
{
    $user = \App\Models\User::findOrFail($id);

    $user->update([
        'failed_login_attempts' => 0,
        'is_locked' => false,
    ]);

    $this->logAdminActivity('account.unlocked', "Unlocked account for {$user->name}.", 'security', $user);

    return back()->with('success', "User {$user->name}'s account has been unlocked successfully.");
}
}