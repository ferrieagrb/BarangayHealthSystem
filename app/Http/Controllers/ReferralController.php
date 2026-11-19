<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\citizens;
use App\Models\CitizenActivityLog;
use App\Services\BhwCitizenAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

        $referrals = Referral::with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('bhw.referrals.index', compact('referrals'));
    }

    public function create(Request $request, BhwCitizenAccess $access)
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

        $request->validate([
            'search' => 'nullable|string|max:255',
            'citizen_id' => 'nullable|integer|min:1',
        ]);
        $query = $access->scopeCitizens(citizens::query(), $request->user());
        $citizen = $request->filled('citizen_id')
            ? (clone $query)->with(['subgroup.purok', 'family.purok'])->findOrFail($request->integer('citizen_id'))
            : null;

        $citizens = (clone $query)
            ->when($search = trim((string) $request->input('search')), function ($query) use ($search) {
                // Match every word so full names work across separate name columns.
                foreach (preg_split('/\s+/', $search) as $word) {
                    $query->where(function ($query) use ($word) {
                        $query->where('Citizen_FName', 'like', '%'.$word.'%')
                            ->orWhere('Citizen_MName', 'like', '%'.$word.'%')
                            ->orWhere('Citizen_LName', 'like', '%'.$word.'%')
                            ->orWhere('id', $word);
                    });
                }
            })
            ->orderBy('Citizen_LName')->orderBy('Citizen_FName')->orderBy('id')
            ->paginate(8)->withQueryString();

        $referral = new Referral($citizen ? $this->patientSnapshot($citizen, $access) : []);
        $referral->referred_at = now();

        return view('bhw.referrals.create', compact('citizens', 'citizen', 'referral'));
    }

    public function store(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') {
            abort(403);
        }

        $validated = $request->validate(['citizen_id' => 'required|integer|min:1']);
        $access = app(BhwCitizenAccess::class);
        $citizen = $access->scopeCitizens(citizens::query(), $request->user())
            ->with(['subgroup.purok', 'family.purok'])->findOrFail($validated['citizen_id']);

        $filePath = null;
        try {
            DB::transaction(function () use ($citizen, $access, &$filePath) {
                // Read patient details from the record; never trust submitted basic info.
                $referral = Referral::create($this->patientSnapshot($citizen, $access) + [
                    'date_of_referral' => today()->toDateString(),
                    'referred_at' => now(),
                    'status' => null,
                    'user_id' => Auth::id(),
                ]);

                $pdf = Pdf::loadView('bhw.referrals.pdf', compact('referral'))->setPaper('a4', 'portrait');
                $filePath = 'referrals/referral_'.$referral->id.'.pdf';
                if (!Storage::disk('local')->put($filePath, $pdf->output())) {
                    throw new \RuntimeException('Unable to save the referral PDF.');
                }
                $referral->update(['file_path' => $filePath]);

                CitizenActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'create',
                    'module' => 'referral',
                    'citizen_id' => $citizen->id,
                    'description' => 'Created medical referral for '.$referral->name,
                ]);
            });
        } catch (\Throwable $exception) {
            if ($filePath !== null) Storage::disk('local')->delete($filePath);
            throw $exception;
        }

        return redirect()->route('referrals.index')
            ->with('success', 'Referral PDF generated successfully.');
    }

    private function patientSnapshot(citizens $citizen, BhwCitizenAccess $access): array
    {
        return [
            'citizen_id' => $citizen->id,
            'name' => implode(' ', array_filter([
                $citizen->Citizen_FName, $citizen->Citizen_MName, $citizen->Citizen_LName,
            ], fn ($part) => $part !== null && trim($part) !== '')),
            'age' => $citizen->ageForEligibility(),
            'gender' => $citizen->Citizen_Sex,
            'birth_date' => $citizen->Citizen_BirthDate,
            'address' => $access->motherPurok($citizen)?->name ?: (string) $citizen->Citizen_Purok,
            'contact_number' => $citizen->Citizen_ContactNo,
        ];
    }

    public function download(Referral $referral)
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

        $path = (string) $referral->file_path;
        abort_if($path === '', 404);

        // Render the saved patient snapshot with today's branding instead of serving an old PDF.
        // Older referrals retain all of their original clinical fields in the legacy template.
        $view = $referral->referred_at ? 'bhw.referrals.pdf' : 'bhw.referrals.legacy-pdf';
        $response = Pdf::loadView($view, compact('referral'))
            ->setPaper('a4', 'portrait')
            ->download(basename($path));
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');

        return $response;
    }
}
