<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(Request $request)
    {
        // Trim inputs
        $username = trim($request->input('Username'));
        $password = trim($request->input('Password'));

        // Check if both fields are empty
        if (empty($username) && empty($password)) {
            return back()->with('error', 'Please enter your username and password.');
        }

        // Check if username is entered but password is missing
        if (!empty($username) && empty($password)) {
            return back()->with('error', 'Incorrect username or password.');
        }

        // Validate presence (Laravel validation) for both fields
        $request->validate([
            'Username' => 'required|string',
            'Password' => 'required|string',
        ]);

        // Find user by email/username
        $user = User::where('email', $username)->first();

        // Check password
        if (!$user || !Hash::check($password, $user->password)) {
            return back()->with('error', 'Incorrect username or password.');
        }

        // Login user
        Auth::login($user);
        $request->session()->regenerate();

        // Redirect based on role
        return match ($user->role) {
            'admin' => redirect()->route('admin.home'),
            'nurse' => redirect()->route('nurse.home'),
            default => redirect()->route('home'),
        };
    }

    public function logout()
    {
        auth()->logout();
        return redirect('/login');
    }
}