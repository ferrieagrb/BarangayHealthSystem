<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Traits\LogsActivity;

class AdminUserManagementController extends Controller
{
    use LogsActivity;

    private function assignableRoles(): array
    {
        return auth()->user()->isSuperAdmin() ? ['superadmin', 'admin', 'bhw', 'citizen'] : ['admin', 'bhw', 'citizen'];
    }

    private function guardTarget(User $user): void
    {
        abort_if($user->role === 'superadmin' && !auth()->user()->isSuperAdmin(), 403, 'Only a super admin can manage super admin accounts.');
    }

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
        $user = User::findOrFail($id);
        $this->guardTarget($user);

        return view('admin.user-view', ['user' => $user]);
    }

    // Marking a user inactive archives them: they can no longer sign in until restored.
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->guardTarget($user);

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
        $this->guardTarget($user);

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
        'username' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
        'role' => ['required', Rule::in($this->assignableRoles())],
    ]);

    // Define the uniform default temporary password for all new users
    $defaultPassword = 'TemporaryPassword123!';

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'username' => $request->filled('username') ? trim($request->username) : null,
        'password' => Hash::make($defaultPassword),
        'role' => $request->role,
        'must_change_password' => true,
    ]);

    $this->logAdminActivity('user.created', "Created user {$user->name} with role {$user->role}.", 'governance', $user, null, ['role' => $user->role]);

    return redirect()->route(auth()->user()->isSuperAdmin() ? 'superadmin.users' : 'admin.users')->with('success', 'User created successfully. Default temporary password is: ' . $defaultPassword);
}

public function update(Request $request, $id)
{
    $user = User::findOrFail($id);
    $this->guardTarget($user);

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => ['required', 'email', 'unique:users,email,' . $user->id],
        'username' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username,' . $user->id],
        'role' => ['required', Rule::in($this->assignableRoles())],
    ]);

    if ((int) $user->id === (int) auth()->id() && $data['role'] !== $user->role) {
        return back()->withErrors(['role' => 'You cannot change your own role.']);
    }

    if ($data['role'] !== $user->role && \App\Models\SystemSetting::passwordReentryRequired()) {
        $request->validate([
            'confirm_password' => ['required', 'string', 'current_password'],
        ], [
            'confirm_password.required' => 'Enter your own password to change a user\'s role.',
            'confirm_password.current_password' => 'Your password is incorrect.',
        ]);
    }

    $user->update([
        'email' => $data['email'],
        'username' => !empty($data['username']) ? trim($data['username']) : null,
        'role' => $data['role'],
    ]);

    $this->logAdminActivity('user.updated', "Updated user {$user->name}.", 'governance', $user);

    return back()->with('success', 'User updated successfully.');
}

public function unlockAccount($id)
{
    $user = \App\Models\User::findOrFail($id);
    $this->guardTarget($user);

    $user->update([
        'failed_login_attempts' => 0,
        'is_locked' => false,
        'locked_until' => null,
    ]);

    $this->logAdminActivity('account.unlocked', "Unlocked account for {$user->name}.", 'security', $user);

    return back()->with('success', "User {$user->name}'s account has been unlocked successfully.");
}

public function resetPassword(Request $request, $id)
{
    $request->validate([
        'confirm_password' => \App\Models\SystemSetting::passwordReentryRequired()
            ? ['required', 'string', 'current_password']
            : ['nullable'],
    ], [
        'confirm_password.required' => 'Enter your own password to reset another user\'s password.',
        'confirm_password.current_password' => 'Your password is incorrect.',
    ]);

    $user = User::findOrFail($id);
    $this->guardTarget($user);

    if ((int) $user->id === (int) auth()->id()) {
        return back()->withErrors(['user' => 'Use your account settings to change your own password.']);
    }

    $temporaryPassword = Str::password(12, symbols: false);

    $user->forceFill([
        'password' => Hash::make($temporaryPassword),
        'must_change_password' => true,
        'failed_login_attempts' => 0,
        'is_locked' => false,
        'locked_until' => null,
    ])->save();

    $this->logAdminActivity('user.password_reset', "Reset the password of {$user->name}; a password change is required at next login.", 'security', $user);

    return back()->with('success', "Password for {$user->name} was reset. Temporary password (shown once): {$temporaryPassword}. They must change it at next login.");
}
}