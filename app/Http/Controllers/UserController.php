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
    $credentials = $request->validate([
        'Username' => 'required',
        'Password' => 'required'
    ]);

    $user = User::where('email', $credentials['Username'])->first();

    if (!$user || !Hash::check($credentials['Password'], $user->password)) {
        return back()->with('error', 'Incorrect username or password.');
    }

    Auth::login($user);
    $request->session()->regenerate();

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