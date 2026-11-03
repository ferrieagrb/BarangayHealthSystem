<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Purok;
use App\Models\SystemSetting;
use App\Models\citizens;
use App\Services\BhwCitizenAccess;
use Illuminate\Support\Facades\Auth;


class EventController extends Controller
{
    /**
     * Show calendar page
     */
    public function index()
    {
        $user = Auth::user();
        $citizenQuery = app(BhwCitizenAccess::class)
            ->scopeCitizens(citizens::with(['vaccinations', 'vaccinationSchedules']), $user)
            ->orderBy('Citizen_LName')
            ->orderBy('Citizen_FName');

        return view('bhw.calendar', [
            'calendarCitizens' => $citizenQuery->get(),
            'calendarPuroks' => Purok::orderBy('name')->get(),
            'vaccinationSchedulingEnabled' => SystemSetting::boolean('vaccination_scheduling_enabled', true),
        ]);
    }

    public function publicindex()
    {
        return view('citizencalendar');
    }

    /**
     * Fetch all events for FullCalendar
     */
    public function fetchEvents()
{
    return response()->json(
        Event::all()->map(function ($event) {
            return [
                'id' => $event->id,   // 🔥 REQUIRED
                'title' => $event->title,
                'start' => $event->start,
                'extendedProps' => [
                    'time' => $event->time,
                    'description' => $event->description,
                ],
            ];
        })
    );
}

    /**
     * Store new event
     */
    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'description' => 'nullable|string|max:2000',
        ]);

        $event = Event::create([
            'title' => $validated['title'],
            'start' => $validated['start'],
            'description' => $validated['description'] ?? null,
            'user_id' => Auth::id(),
        ]);

        return response()->json($event);
    }

    public function updateEvent(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'description' => 'nullable|string|max:2000',
        ]);

        $event = Event::findOrFail($id);
        $event->update([
            'title' => $validated['title'],
            'start' => $validated['start'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json($event);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json([
            'message' => 'Event archived successfully'
        ]);
    }


}