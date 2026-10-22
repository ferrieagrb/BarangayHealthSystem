<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;

class AnnouncementController extends Controller
{
    use LogsActivity;
    // SHOW PAGE
    public function index()
    {
        $announcements = Announcement::with('images')->latest()->get();

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
            $announcement->load('images');
            $this->publishToFacebookPage($announcement);
        }

        return redirect()->route('announcements')
            ->with('success', 'Announcement posted successfully!');
    }

    /**
     * Post message directly to Facebook Page Feed using Meta Graph API
     */
    private function publishToFacebookPage(Announcement $announcement)
    {
        $pageId = config('services.facebook.page_id');
        $accessToken = config('services.facebook.access_token');

        if (!$pageId || !$accessToken) {
            Log::warning('Facebook Page credentials not configured in .env');
            $this->logAuditEvent('facebook.post_skipped', 'Facebook posting skipped because page credentials are not configured.');
            return;
        }

        $message = "📢 [{$announcement->category}] {$announcement->title}\n\n{$announcement->description}";

        try {
            $image = $announcement->images->first();
            $endpoint = $image
                ? "https://graph.facebook.com/{$pageId}/photos"
                : "https://graph.facebook.com/{$pageId}/feed";

            $payload = [
                'access_token' => $accessToken,
            ];

            if ($image) {
                $payload['caption'] = $message;
                $payload['published'] = true;

                $imageContents = Storage::disk('public')->get($image->path);

                $response = Http::timeout(30)
                    ->attach(
                        'source',
                        $imageContents,
                        basename($image->path)
                    )
                    ->post($endpoint, $payload);
            } else {
                $payload['message'] = $message;
                $response = Http::timeout(20)->post($endpoint, $payload);
            }

            if ($response->failed()) {
                Log::error('Facebook Post Error: ' . $response->body());
                $this->logAdminActivity('facebook.post_failed', 'Facebook announcement posting failed: ' . substr($response->body(), 0, 500), 'integration');
            } else {
                $facebookPostId = $response->json('post_id') ?: $response->json('id');
                $announcement->update([
                    'facebook_post_id' => $facebookPostId,
                ]);

                if (!$facebookPostId) {
                    Log::warning('Facebook post succeeded without a deletable post ID: ' . $response->body());
                }
                $this->logAdminActivity(
                    'facebook.posted',
                    $image ? 'Announcement photo posted successfully to Facebook.' : 'Announcement posted successfully to Facebook.',
                    'integration'
                );
            }
        } catch (\Exception $e) {
            Log::error('Facebook API Exception: ' . $e->getMessage());
            $this->logAdminActivity('facebook.post_failed', 'Facebook announcement posting threw an exception: ' . $e->getMessage(), 'integration');
        }
    }

    public function destroy(Announcement $announcement)
    {
        $pageId = config('services.facebook.page_id');
        $accessToken = config('services.facebook.access_token');
        $facebookDeleteFailed = false;

        if ($announcement->facebook_post_id && $pageId && $accessToken) {
            try {
                $response = Http::timeout(15)->delete(
                    'https://graph.facebook.com/' . $announcement->facebook_post_id,
                    ['access_token' => $accessToken]
                );

                if ($response->failed()) {
                    $facebookDeleteFailed = true;
                    Log::warning('Facebook announcement deletion failed: ' . $response->body());
                    $this->logAdminActivity('facebook.delete_failed', 'Facebook announcement deletion failed.', 'integration');
                }
            } catch (\Throwable $exception) {
                $facebookDeleteFailed = true;
                report($exception);
                $this->logAdminActivity('facebook.delete_failed', 'Facebook announcement deletion threw an exception.', 'integration');
            }
        }

        foreach ($announcement->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $announcement->delete();

        $message = $facebookDeleteFailed
            ? 'Announcement deleted locally, but Facebook deletion failed. Check the Facebook access token and permissions.'
            : ($announcement->facebook_post_id
                ? 'Announcement deleted from the system and Facebook.'
                : 'Announcement deleted locally. It has no tracked Facebook post ID.');

        return redirect()->route('announcements')->with($facebookDeleteFailed ? 'error' : 'success', $message);
    }
}