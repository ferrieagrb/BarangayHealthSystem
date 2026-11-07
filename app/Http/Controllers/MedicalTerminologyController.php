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
        abort_unless($user && in_array($user->role, ['bhw', 'admin', 'superadmin', 'citizen'], true), 403);

        $validated = $request->validate([
            'type' => 'required|string|in:medicine,vaccine,diagnosis,supply',
            'q' => 'required|string|min:2|max:100',
            'category' => 'required_if:type,supply|nullable|string|max:255',
        ]);

        return response()
            ->json($terminology->search($validated['type'], trim($validated['q']), $user, $validated['category'] ?? null))
            ->header('Cache-Control', 'private, no-store');
    }
}
