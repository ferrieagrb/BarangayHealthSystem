<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ReferralController extends Controller
{
    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

        $referrals = Referral::latest()->get();

        return view('bhw.referrals.index', compact('referrals'));
    }

    public function create()
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

        return view('bhw.referrals.create');
    }

    public function store(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

        $validated = $request->validate([
            'date_of_referral' => 'required|date',
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:0',
            'gender' => 'required|string|max:50',
            'address' => 'required|string',
            'requests_for' => 'required|string',
            'vital_signs' => 'nullable|string',
            'treatment_given' => 'nullable|string',
            'medication_given' => 'nullable|string',
            'self_medication' => 'nullable|string',
            'maintenance_schedule' => 'nullable|string',
            'referred_by' => 'required|string|max:255',
        ]);

        $validated['status'] = 'approved';

        $referral = Referral::create($validated);

        $pdf = Pdf::loadView('bhw.referrals.pdf', compact('referral'));

        $fileName = 'referral_' . $referral->id . '_' . time() . '.pdf';
        $filePath = 'storage/referrals/' . $fileName;

        file_put_contents(
            public_path('storage/referrals/' . $fileName),
            $pdf->output()
        );

        $referral->update([
            'file_path' => $filePath,
        ]);

        return redirect()->route('referrals.index')
            ->with('success', 'Referral created and PDF generated successfully.');
    }

    public function updateStatus(Request $request, Referral $referral)
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

        $request->validate([
            'status' => 'required|in:approved,released,returned',
        ]);

        $referral->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Referral status updated successfully.');
    }

    public function download(Referral $referral)
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

        return response()->download(
            public_path($referral->file_path)
        );
    }
}