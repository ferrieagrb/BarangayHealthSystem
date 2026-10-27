<?php

namespace App\Http\Controllers;

use App\Models\citizens;
use App\Models\CitizenActivityLog;
use App\Models\HealthRecord;
use App\Models\HealthRecordActivityLog;
use App\Models\UserPermission;
use App\Services\BhwCitizenAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class QRScannerController extends Controller
{
    /**
     * Show the authenticated BHW QR scanner.
     */
    public function index(): View
    {
        return view('bhw.qr_scanner');
    }

    /**
     * Look up a citizen by the unique QR token.
     *
     * This endpoint is intended for authenticated, authorized BHW users.
     * It returns only the fields needed by the scanner interface.
     */
    public function lookup(string $token): JsonResponse
    {
        $query = citizens::query()->with(['vaccinations', 'medications', 'healthRecords', 'family']);
        if (preg_match('/^legacy-id-(\d+)$/', $token, $matches)) {
            $query->whereKey($matches[1]);
        } else {
            $query->where('qr_token', $token);
        }
        $citizen = $query->first();

        if (!$citizen) {
            return response()->json([
                'message' => 'No citizen record matches this QR code.',
            ], 404);
        }

        abort_unless($this->canAccessCitizen($citizen), 403, 'You are not assigned to this citizen record.');

        return response()->json([
            'citizen' => [
                'id' => $citizen->id,
                'first_name' => $citizen->Citizen_FName,
                'last_name' => $citizen->Citizen_LName,
                'full_name' => trim($citizen->Citizen_FName . ' ' . $citizen->Citizen_LName),
                'birth_date' => $citizen->Citizen_BirthDate,
                'age' => $citizen->Citizen_Age,
                'contact_number' => $citizen->Citizen_ContactNo,
                'purok' => $citizen->Citizen_Purok,
                'sex' => $citizen->Citizen_Sex,
                'height' => $citizen->Citizen_Height,
                'weight' => $citizen->Citizen_Weight,
                'family_id' => $citizen->family_id,
                'housing_status' => $citizen->Citizen_HousingStatus,
                'pregnant' => $citizen->Citizen_Pregnant ? 'Yes' : 'No',
                'in_pills' => $citizen->Citizen_InPills ? 'Yes' : 'No',
                'is_deceased' => $citizen->Citizen_IsDeceased ? 'Deceased' : 'Active',
                'photo_url' => $citizen->photo_path
                    ? asset('storage/' . ltrim($citizen->photo_path, '/'))
                    : null,
            ],
            'vaccinations' => $citizen->vaccinations->map(fn ($record) => [
                'vaccine_name' => $record->vaccine_name,
                'dose_number' => $record->dose_number,
                'date_administered' => $record->date_administered,
                'administered_by' => $record->administered_by,
            ])->values(),
            'medications' => $citizen->medications->map(fn ($record) => [
                'medicine_name' => $record->medicine_name,
                'dosage' => $record->dosage,
                'quantity_dispensed' => $record->quantity_dispensed,
                'date_dispensed' => $record->date_dispensed,
            ])->values(),
            'health_records' => $citizen->healthRecords->map(fn ($record) => [
                'diagnosis' => $record->diagnosis,
                'record_date' => $record->record_date,
                'comments' => $record->comments,
            ])->values(),
        ]);
    }

    /**
     * Save a diagnosis submitted from the QR checker.
     */
    public function storeDiagnosis(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'citizen_id' => 'required|integer|exists:citizens,id',
            'diagnosis' => 'required|string|max:255',
            'record_date' => 'required|date',
            'comments' => 'nullable|string|max:2000',
        ]);

        $citizen = citizens::with('family')->findOrFail($validated['citizen_id']);
        abort_unless($this->canAccessCitizen($citizen), 403, 'You are not assigned to this citizen record.');
        abort_if((int) $citizen->Citizen_IsDeceased === 1, 403, 'Cannot add a diagnosis to a deceased citizen record.');

        $record = HealthRecord::create([
            'citizen_id' => $citizen->id,
            'diagnosis' => trim($validated['diagnosis']),
            'record_date' => $validated['record_date'],
            'comments' => isset($validated['comments']) ? trim($validated['comments']) : null,
        ]);

        HealthRecordActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'citizen_id' => $citizen->id,
            'health_record_id' => $record->id,
            'description' => 'Added health record: ' . $record->diagnosis,
        ]);

        CitizenActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'module' => 'health_record',
            'citizen_id' => $citizen->id,
            'description' => 'Added health record: ' . $record->diagnosis,
        ]);

        return response()->json([
            'message' => 'Diagnosis added successfully.',
            'record_id' => $record->id,
        ], 201);
    }

    private function canAccessCitizen(citizens $citizen): bool
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'bhw') {
            return false;
        }

        $canViewAll = UserPermission::where('user_id', $user->id)
            ->where('feature', 'view_all_citizens')
            ->where('access', '!=', 'none')
            ->exists();

        if ($canViewAll) {
            return true;
        }

        $assignedSubgroupIds = app(BhwCitizenAccess::class)->assignedSubgroupIds($user);
        $familySubgroupId = $citizen->family?->subgroup_id;

        return $assignedSubgroupIds->contains($citizen->subgroup_id)
            || $assignedSubgroupIds->contains($familySubgroupId);
    }

    /**
     * Public QR landing page. It intentionally does not reveal health data.
     * Health records must be retrieved through an authenticated provider portal.
     */
    public function publicHealthId(string $token): View
    {
        return view('health-id.public');
    }
}
