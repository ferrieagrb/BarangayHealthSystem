<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    // SHOW PAGE
    public function index()
    {
        $announcements = Announcement::latest()->get();

        $totalPosts = Announcement::count();
        $thisMonth = Announcement::whereMonth('created_at', now()->month)->count();
        $urgentAlerts = Announcement::where('category', 'Urgent Alert')->count();

        return view('bhw.announcements', compact(
            'announcements',
            'totalPosts',
            'thisMonth',
            'urgentAlerts'
        ));
    }

    public function publicIndex()
{
    // Fetch all announcements ordered by latest
    $announcements = Announcement::latest()->get();

    return view('citizenannouncements', compact('announcements'));
}

    // STORE DATA
    public function store(Request $request)
{
    $request->validate([
        'title' => 'required',
        'description' => 'required',
        'category' => 'required',
    ]);

    \App\Models\Announcement::create([
        'title' => $request->title,
        'description' => $request->description,
        'category' => $request->category,
    ]);

    return redirect()->route('announcements')
        ->with('success', 'Announcement posted!');
}
}