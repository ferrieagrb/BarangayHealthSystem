<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;


abstract class Controller
{
    public function login(Request $request){
        $Incoming = $request->validate([
            'Username' => 'required',
            'Password' => 'required'
        ]);

        if (auth()->attempt(['name' => $Incoming['Username'],'password' => $Incoming['Password']])){
            $user = auth()->user();
            if ($user->admin == 1) {
            auth()->logout(); // log them out immediately
            return back()->withErrors([
                'Username' => 'Admins cannot login here.'
            ])->withInput();
            }
            $request->session()->regenerate();
            return redirect('/dashboard');
        }
        return back()->withErrors([
        'Password' => 'Incorrect username or password.'
        ])->withInput();
    }

    public function logout(){
        auth()->logout();   
        return redirect('/');
    }
}
