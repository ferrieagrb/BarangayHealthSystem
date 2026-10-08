<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark',
            'font_size' => 'required|in:normal,large,xl',
        ]);

        $user = auth()->user();
        $user->update([
            'theme' => $request->theme,
            'font_size' => $request->font_size,
        ]);

        return back()->with('success', 'Preferences updated successfully.');
    }

    public function edit()
    {
        $user = auth()->user();
        return view('settings', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = auth()->user();
        
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false, // Clears flag if they had a forced reset pending
        ]);

        return back()->with('password_success', 'Password changed successfully.');
    }
}
