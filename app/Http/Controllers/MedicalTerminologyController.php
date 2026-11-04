<?php

namespace App\Http\Controllers;

use App\Services\MedicalTerminologySearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicalTerminologyController extends Controller
{
    public function search(Request $request, MedicalTerminologySearch $terminology): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'bhw', 403);

        $validated = $request->validate([
            'type' => 'required|string|in:medicine,vaccine,diagnosis',
            'q' => 'required|string|min:2|max:100',
        ]);

        return response()
            ->json($terminology->search($validated['type'], trim($validated['q']), $user))
            ->header('Cache-Control', 'private, no-store');
    }
}
