<?php

namespace App\Http\Controllers;

use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ConfirmPasswordController extends Controller
{
    use LogsActivity;

    public function show()
    {
        return view('auth.confirm-password');
    }

    public function store(Request $request)
    {
        $request->validate(['password' => ['required', 'string']]);

        if (!Hash::check($request->input('password'), $request->user()->password)) {
            $this->logAuditEvent('password.confirm_failed', 'Password confirmation failed for a sensitive action.');

            return back()->withErrors(['password' => 'The password you entered is incorrect.']);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route($request->user()->isSuperAdmin() ? 'superadmin.dashboard' : 'home'));
    }
}
