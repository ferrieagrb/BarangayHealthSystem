<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

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

        // Validate presence for both fields
        $request->validate([
            'Username' => 'required|string',
            'Password' => 'required|string',
        ]);

        // Create a unique rate limiter key based on username and IP address
        $throttleKey = Str::lower($username) . '|' . $request->ip();

        // 1. Check Laravel's temporary Rate Limiter first
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Too many login attempts. Please try again in {$seconds} seconds.");
        }

        // 2. Find user by email/username
        $user = User::where('email', $username)->first();

        if (!$user) {
            RateLimiter::hit($throttleKey, 60); // Count failed attempt against the rate limiter
            return back()->with('error', 'Incorrect username or password.');
        }

        // 3. Check if the database account is permanently locked by an admin
        if ($user->is_locked) {
            return back()->with('error', 'Your account has been locked due to excessive failed login attempts. Please contact an Administrator or SuperAdmin to unlock it.');
        }

        // 4. Verify password
        if (!Hash::check($password, $user->password)) {
            // Hit the temporary rate limiter (blocks for 60 seconds after threshold)
            RateLimiter::hit($throttleKey, 60);

            // Increment permanent database counter
            $user->increment('failed_login_attempts');

            // Check if permanent lockout threshold (10 attempts) has been reached
            if ($user->failed_login_attempts >= 10) {
                $user->update(['is_locked' => true]);
                
                return back()->with('error', 'Your account has been locked after 10 failed login attempts. Please contact an Administrator or SuperAdmin to unlock it.');
            }

            $attemptsLeft = 10 - $user->failed_login_attempts;
            return back()->with('error', "Incorrect username or password. You have {$attemptsLeft} attempt(s) left before your account is locked.");
        }

        // 5. Successful Login: Clear temporary rate limiter and reset database lock/attempt counters
        RateLimiter::clear($throttleKey);
        $user->update([
            'failed_login_attempts' => 0,
            'is_locked' => false
        ]);

        // Login user
        Auth::login($user);
        $request->session()->regenerate();

        // First-login password reset check
        if ($user->must_change_password) {
            return redirect()->route('password.force-change');
        }

        // Redirect based on role
        return match ($user->role) {
            'superadmin' => redirect()->route('superadmin.dashboard'),
            'admin' => redirect()->route('admin.home'),
            'bhw' => redirect()->route('home'),
            'citizen' => redirect()->route('citizen.dashboard'),
            default => redirect()->route('landing'),
        };
    }

    public function logout()
    {
        auth()->logout();
        return redirect('/login');
    }
}