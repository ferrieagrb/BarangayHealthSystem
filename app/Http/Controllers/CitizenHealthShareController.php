<?php

namespace App\Http\Controllers;

use App\Models\citizens;
use App\Models\CitizenHealthShare;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitizenHealthShareController extends Controller
{
    public function grant(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user?->isCitizen() && $user->citizen_id, 403);

        $now = now();
        DB::transaction(function () use ($user, $now): void {
            $citizen = citizens::whereKey($user->citizen_id)->lockForUpdate()->firstOrFail();
            $citizen->public_health_id_enabled = true;
            $citizen->save();

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

        $now = now();
        DB::transaction(function () use ($user, $now): void {
            $citizen = citizens::whereKey($user->citizen_id)->lockForUpdate()->firstOrFail();
            $citizen->public_health_id_enabled = false;
            $citizen->save();

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
    }
}
