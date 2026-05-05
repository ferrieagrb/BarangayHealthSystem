<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class signedURL extends Controller
{
        public function openCitizenList()
        {
            $url = URL::temporarySignedRoute(
                'citizenlist',
                now()->addMinutes(30)
            );

            return redirect($url);
        }

        public function openHome()
        {
            $url = URL::temporarySignedRoute(
                'home',
                now()->addMinutes(30)
            );

            return redirect($url);
        }
}
