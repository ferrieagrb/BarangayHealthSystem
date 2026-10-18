<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsActivity;

class AnnouncementController extends Controller
{
    use LogsActivity;
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
        $announcements = Announcement::with('images')
            ->whereHas('images')
            ->latest()
            ->get();

        return view('citizenannouncements', compact('announcements'));
    }

    public function publicShow(Announcement $announcement)
    {
        abort_unless($announcement->images()->exists(), 404);

        $announcement->load('images');

        return view('citizenannouncement_show', compact('announcement'));
    }

    // STORE DATA
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'category' => 'required',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'post_to_facebook' => 'nullable|boolean',
        ]);

        $announcement = DB::transaction(function () use ($request) {
            $announcement = Announcement::create([
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'user_id' => Auth::id(),
            ]);

            foreach ($request->file('images', []) as $sortOrder => $image) {
                $announcement->images()->create([
                    'path' => $image->store('announcements', 'public'),
                    'sort_order' => $sortOrder,
                ]);
            }

            return $announcement;
        });

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
            $this->logAuditEvent('facebook.post_skipped', 'Facebook posting skipped because page credentials are not configured.');
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
                $this->logAdminActivity('facebook.post_failed', 'Facebook announcement posting failed: ' . substr($response->body(), 0, 500), 'integration');
            } else {
                $this->logAdminActivity('facebook.posted', 'Announcement posted successfully to Facebook.', 'integration');
            }
        } catch (\Exception $e) {
            Log::error('Facebook API Exception: ' . $e->getMessage());
            $this->logAdminActivity('facebook.post_failed', 'Facebook announcement posting threw an exception: ' . $e->getMessage(), 'integration');
        }
    }
}