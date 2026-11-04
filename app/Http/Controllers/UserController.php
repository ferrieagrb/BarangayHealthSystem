<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\SystemSetting;
use App\Traits\LogsActivity;

class UserController extends Controller
{
    use LogsActivity;

    private const LOCKOUT_MINUTES = 15;
    public function login(Request $request)
    {
        // Trim inputs
        $username = trim($request->input('Username'));
        $password = trim($request->input('Password'));
        
        // Check if both fields are empty
        if (empty($username) && empty($password)) {
            $this->logAuditEvent('login.validation_failed', 'Login submitted without credentials.');
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
            $this->logAuditEvent('login.rate_limited', "Login rate limited for identifier {$username}.");
            return back()->with('error', "Too many login attempts. Please try again in {$seconds} seconds.");
        }

        // 2. Find user by email/username
        $maxFailedAttempts = max(3, (int) SystemSetting::value('max_failed_login_attempts', 10));
        $user = User::where('email', $username)->orWhere('username', $username)->first();

        if (!$user) {
            return $this->failUnknownIdentifier($username, $password, $throttleKey, $maxFailedAttempts);
        }

        // 3. A temporary lockout clears itself once it has expired.
        if ($user->locked_until && $user->locked_until->isPast()) {
            $user->update(['locked_until' => null, 'failed_login_attempts' => 0]);
        }

        if ($user->isLockedOut()) {
            $this->logAuditEvent('login.blocked', "Login blocked for locked account {$user->email}.", $user->id);
            return back()->with('error', $this->lockedMessage($user->locked_until?->getTimestamp()));
        }

        // 4. Verify password
        if (!Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            $user->increment('failed_login_attempts');

            if ($user->failed_login_attempts >= $maxFailedAttempts) {
                $until = now()->addMinutes(self::LOCKOUT_MINUTES);
                $user->update(['locked_until' => $until]);
                $this->logAuditEvent('account.locked', "Account {$user->email} was temporarily locked until {$until->toDateTimeString()} after repeated failed logins.", $user->id);

                return back()->with('error', $this->lockedMessage($until->getTimestamp()));
            }

            $attemptsLeft = $maxFailedAttempts - $user->failed_login_attempts;
            $this->logAuditEvent('login.failed', "Failed password login for {$user->email}; {$attemptsLeft} attempts remaining.", $user->id);
            return back()->with('error', $this->attemptsLeftMessage($attemptsLeft));
        }

        // 5. Successful Login: Clear temporary rate limiter and reset database lock/attempt counters
        RateLimiter::clear($throttleKey);
        $user->update([
            'failed_login_attempts' => 0,
            'is_locked' => false,
            'locked_until' => null,
        ]);
        // Login user
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('auth.password_confirmed_at');
        $this->logAuditEvent('login.success', "Successful login for {$user->email}.", $user->id);

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

    private function lockedMessage(?int $untilTimestamp): string
    {
        if (!$untilTimestamp) {
            return 'Too many failed sign-in attempts. Please ask an Administrator to unlock your account.';
        }

        $minutes = max(1, (int) ceil(($untilTimestamp - time()) / 60));

        return "Too many failed sign-in attempts. Please try again in {$minutes} minute(s), or ask an Administrator to unlock your account.";
    }

    private function attemptsLeftMessage(int $attemptsLeft): string
    {
        return "Incorrect username or password. You have {$attemptsLeft} attempt(s) left before your account is temporarily locked.";
    }

    // Unknown identifiers get the same counting, wording and timing as real accounts so accounts cannot be enumerated.
    private function failUnknownIdentifier(string $username, string $password, string $throttleKey, int $maxFailedAttempts)
    {
        Hash::make($password);
        RateLimiter::hit($throttleKey, 60);

        $key = 'login-unknown:' . sha1(Str::lower($username));
        $lockedUntil = (int) Cache::get($key . ':until', 0);

        if ($lockedUntil > time()) {
            return back()->with('error', $this->lockedMessage($lockedUntil));
        }

        $ttl = now()->addMinutes(self::LOCKOUT_MINUTES);
        Cache::add($key, 0, $ttl);
        $attempts = Cache::increment($key);

        $this->logAuditEvent('login.failed', "Login failed for unknown identifier {$username}.");

        if ($attempts >= $maxFailedAttempts) {
            Cache::put($key . ':until', $ttl->getTimestamp(), $ttl);
            Cache::forget($key);

            return back()->with('error', $this->lockedMessage($ttl->getTimestamp()));
        }

        return back()->with('error', $this->attemptsLeftMessage($maxFailedAttempts - $attempts));
    }

    public function logout()
    {
        $userId = Auth::id();
        $this->logAuditEvent('logout', 'User logged out.', $userId);
        auth()->logout();
        return redirect('/login');
    }
}