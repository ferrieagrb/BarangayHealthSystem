<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Show calendar page
     */
    public function index()
    {
        return view('bhw.calendar');
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
        $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
        ]);

        $event = Event::create([
            'title' => $request->title,
            'start' => $request->start,
        ]);

        return response()->json($event);
    }

    public function destroy($id)
{
    $event = Event::findOrFail($id);
    $event->delete();

    return response()->json([
        'message' => 'Event deleted successfully'
    ]);
}


}