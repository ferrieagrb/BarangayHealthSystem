<?php

namespace App\Http\Controllers;

use App\Models\citizens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CitizenHealthShareController extends Controller
{
    public function grant(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isCitizen() && $user->citizen_id, 403);

        $citizen = citizens::findOrFail($user->citizen_id);
        $citizen->public_health_id_enabled = true;
        $citizen->save();

        return back()->with('success', 'Public viewing of your Health ID record is enabled. You can turn it off at any time.');
    }

    public function revoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isCitizen() && $user->citizen_id, 403);

        $citizen = citizens::findOrFail($user->citizen_id);
        $citizen->public_health_id_enabled = false;
        $citizen->save();

        return back()->with('success', 'Public viewing of your Health ID record is turned off.');
    }
}
