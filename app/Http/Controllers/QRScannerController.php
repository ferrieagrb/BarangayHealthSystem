<?php

namespace App\Http\Controllers;

use App\Models\citizens;
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
        $citizen = citizens::query()
            ->with(['vaccinations', 'medications', 'healthRecords'])
            ->where('qr_token', $token)
            ->first();

        if (!$citizen) {
            return response()->json([
                'message' => 'No citizen record matches this QR code.',
            ], 404);
        }

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
     * Public QR landing page. It intentionally does not reveal health data.
     * Health records must be retrieved through an authenticated provider portal.
     */
    public function publicHealthId(string $token): View
    {
        return view('health-id.public');
    }
}
