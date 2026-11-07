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

        DB::transaction(function () use ($citizen, $user, $now): void {
            citizens::whereKey($citizen->id)->lockForUpdate()->firstOrFail();
            CitizenHealthShare::query()
                ->active()
                ->where('citizen_id', $citizen->id)
                ->update(['revoked_at' => $now, 'updated_at' => $now]);

            CitizenHealthShare::create([
                'citizen_id' => $citizen->id,
                'granted_by_user_id' => $user->id,
                'granted_at' => $now,
                'expires_at' => null,
            ]);
        });

        return back()->with('success', 'Public QR health sharing is on until you turn it off.');
    }

    public function revoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isCitizen() && $user->citizen_id, 403);

<<<<<<< HEAD
        $citizen = citizens::findOrFail($user->citizen_id);
        $citizen->public_health_id_enabled = false;
        $citizen->save();

        return back()->with('success', 'Public viewing of your Health ID record is turned off.');
=======
        $now = now();
        DB::transaction(function () use ($user, $now): void {
            citizens::whereKey($user->citizen_id)->lockForUpdate()->firstOrFail();
            CitizenHealthShare::query()->active()->where('citizen_id', $user->citizen_id)
                ->update(['revoked_at' => $now, 'updated_at' => $now]);
            $latest = CitizenHealthShare::where('citizen_id', $user->citizen_id)
                ->latest('granted_at')->latest('id')->first();
            if (!$latest) {
                CitizenHealthShare::create([
                    'citizen_id' => $user->citizen_id, 'granted_by_user_id' => $user->id,
                    'granted_at' => $now, 'expires_at' => null, 'revoked_at' => $now,
                ]);
            } elseif ($latest->revoked_at === null) {
                $latest->update(['revoked_at' => $now]);
            }
        });

        return back()->with('success', 'Public QR health sharing is off. Scanning your QR will no longer show your health information.');
>>>>>>> 9c90e16b55532dc40651f41341bb26b6d7471633
    }
}
