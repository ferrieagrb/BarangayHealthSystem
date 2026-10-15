<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            'post_to_facebook' => 'nullable|boolean',
        ]);

        $announcement = Announcement::create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
        ]);

        // Cross-post to Facebook Page if checked
        if ($request->boolean('post_to_facebook')) {
            $this->publishToFacebookPage($announcement->title, $announcement->description, $announcement->category);
        }

        return redirect()->route('announcements')
            ->with('success', 'Announcement posted successfully!');
    }

    /**
     * Post message directly to Facebook Page Feed using Meta Graph API
     */
    private function publishToFacebookPage(string $title, string $description, string $category)
    {
        $pageId = config('services.facebook.page_id');
        $accessToken = config('services.facebook.access_token');

        if (!$pageId || !$accessToken) {
            Log::warning('Facebook Page credentials not configured in .env');
            return;
        }

        // Format post message text
        $message = "📢 [{$category}] {$title}\n\n{$description}";

        try {
            $response = Http::post("https://graph.facebook.com/v26.0/{$pageId}/feed", [
                'message' => $message,
                'access_token' => $accessToken,
            ]);

            if ($response->failed()) {
                Log::error('Facebook Post Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Facebook API Exception: ' . $e->getMessage());
        }
    }
}