<?php

namespace App\Http\Controllers;

use App\Models\CitizenHealthShare;
use App\Models\citizens;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitizenHealthShareController extends Controller
{
    public function grant(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isCitizen() && $user->citizen_id, 403);
        $request->validate([
            'consent' => ['accepted'],
        ]);

        $citizen = citizens::findOrFail($user->citizen_id);
        $now = now();

        DB::transaction(function () use ($citizen, $user, $now): void {
            CitizenHealthShare::query()
                ->active()
                ->where('citizen_id', $citizen->id)
                ->update(['revoked_at' => $now, 'updated_at' => $now]);

            CitizenHealthShare::create([
                'citizen_id' => $citizen->id,
                'granted_by_user_id' => $user->id,
                'granted_at' => $now,
                'expires_at' => $now->copy()->addDays(7),
            ]);
        });

        return back()->with('success', 'Health record sharing is enabled for 7 days. You can revoke access at any time.');
    }

    public function revoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isCitizen() && $user->citizen_id, 403);

        $now = now();
        CitizenHealthShare::query()
            ->active()
            ->where('citizen_id', $user->citizen_id)
            ->update(['revoked_at' => $now, 'updated_at' => $now]);

        return back()->with('success', 'Health record sharing has been revoked.');
    }
}
