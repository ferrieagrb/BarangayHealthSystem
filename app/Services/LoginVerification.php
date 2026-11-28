<?php

namespace App\Services;

use App\Models\LoginChallenge;
use App\Models\User;
use App\Notifications\LoginConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginVerification
{
    public function start(Request $request, User $user): void
    {
        if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'login_confirmation' => 'Your account needs a valid email address for sign-in confirmation. Please ask an Administrator to update it.',
            ]);
        }

        $key = 'login-email:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            throw ValidationException::withMessages([
                'login_confirmation' => 'Please wait '.RateLimiter::availableIn($key).' seconds before requesting another sign-in email.',
            ]);
        }
        RateLimiter::hit($key, 60);

        // Replacing a challenge only cancels the attempt in this browser.
        $this->cancel($request);
        $code = (string) random_int(100000, 999999);
        $challenge = LoginChallenge::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'email' => $user->email,
            'password_fingerprint' => hash('sha256', $user->password),
            'code_hash' => Hash::make($code),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'expires_at' => now()->addMinutes(10),
        ]);

        try {
            $user->notify(new LoginConfirmation($challenge, $code));
        } catch (\Throwable $exception) {
            $challenge->delete();
            report($exception);
            throw ValidationException::withMessages([
                'login_confirmation' => 'The sign-in email could not be sent. Please try again later or contact an Administrator.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('login_challenge_id', $challenge->id);
    }

    public function cancel(Request $request): void
    {
        $id = $request->session()->pull('login_challenge_id');
        if ($id) {
            LoginChallenge::whereKey($id)->whereNull('consumed_at')->update(['consumed_at' => now()]);
        }
    }
}
