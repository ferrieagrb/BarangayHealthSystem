<?php

namespace App\Http\Controllers;

use App\Models\LoginChallenge;
use App\Services\LoginVerification;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginConfirmationController extends Controller
{
    use LogsActivity;

    public function show(Request $request)
    {
        $challenge = LoginChallenge::find($request->session()->get('login_challenge_id'));
        if (! $challenge || ! $challenge->isPending() || ! $challenge->matchesAccount($challenge->user)) {
            $request->session()->forget('login_challenge_id');

            return redirect()->route('login')->with('error', 'This sign-in attempt expired or was blocked. Please sign in again.');
        }

        [$name, $domain] = explode('@', $challenge->email, 2);

        return view('auth.login-code', [
            'email' => mb_substr($name, 0, 1).'***@'.$domain,
            'approved' => (bool) $challenge->approved_at,
        ]);
    }

    // Reading an email link never approves or denies a sign-in (mail scanners may open it).
    public function review(LoginChallenge $challenge)
    {
        return view('auth.login-review', ['challenge' => $challenge, 'result' => null]);
    }

    public function decide(Request $request, LoginChallenge $challenge)
    {
        $request->validate(['decision' => ['required', 'in:yes,no']]);
        $result = DB::transaction(function () use ($request, $challenge) {
            $challenge = LoginChallenge::whereKey($challenge->id)->lockForUpdate()->firstOrFail();
            if (! $challenge->isPending() || ! $challenge->matchesAccount($challenge->user)) {
                return 'unavailable';
            }

            if ($request->input('decision') === 'no') {
                $challenge->update(['denied_at' => now()]);
                $this->logAuditEvent('login.denied', 'Email owner rejected a sign-in attempt.', $challenge->user_id);

                return 'denied';
            }

            $challenge->update(['approved_at' => $challenge->approved_at ?? now()]);

            return 'approved';
        });

        return view('auth.login-review', ['challenge' => $challenge->fresh(), 'result' => $result]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => ['required', 'regex:/^[0-9]{6}$/']]);
        $outcome = DB::transaction(function () use ($request) {
            $challenge = LoginChallenge::whereKey($request->session()->get('login_challenge_id'))->lockForUpdate()->first();
            if (! $challenge || ! $challenge->isPending() || ! $challenge->matchesAccount($challenge->user)) {
                return 'This sign-in attempt expired or was blocked. Please sign in again.';
            }
            // Count wrong codes even before approval to prevent unlimited guessing.
            if (! Hash::check($request->input('code'), $challenge->code_hash)) {
                $challenge->increment('attempts');

                return $challenge->attempts >= 5
                    ? 'Too many incorrect codes. Please sign in again.'
                    : 'Incorrect verification code. Please check your email.';
            }
            if (! $challenge->approved_at) {
                return 'Open the email link and choose "Yes, this is me" before submitting your code.';
            }

            $challenge->update(['consumed_at' => now()]);

            return $challenge->user;
        });

        if (is_string($outcome)) {
            throw ValidationException::withMessages(['code' => $outcome]);
        }

        Auth::login($outcome);
        $request->session()->regenerate();
        $request->session()->forget(['login_challenge_id', 'auth.password_confirmed_at']);
        $this->logAuditEvent('login.success', 'Successful login after email confirmation and verification code.', $outcome->id);

        return redirect()->route($outcome->must_change_password ? 'password.force-change' : 'dashboard');
    }

    public function resend(Request $request, LoginVerification $verification)
    {
        $challenge = LoginChallenge::find($request->session()->get('login_challenge_id'));
        if (! $challenge || ! $challenge->isPending() || ! $challenge->matchesAccount($challenge->user)) {
            return redirect()->route('login')->with('error', 'Please sign in again to request a new code.');
        }
        $verification->start($request, $challenge->user);

        return redirect()->route('login.confirmation')->with('status', 'A new email was sent. Use its confirmation link and code; the previous ones no longer work.');
    }

    public function cancel(Request $request, LoginVerification $verification)
    {
        $verification->cancel($request);

        return redirect()->route('login');
    }
}
