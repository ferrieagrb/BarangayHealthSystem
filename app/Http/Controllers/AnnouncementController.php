<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\AnnouncementImage;
use App\Jobs\DispatchAnnouncementPush;
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
            ->latest()
            ->get();

        return view('citizenannouncements', compact('announcements'));
    }

    public function publicShow(Announcement $announcement)
    {
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
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
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

        DispatchAnnouncementPush::dispatch($announcement->id);

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

        $message = "{$announcement->title} - {$announcement->category}\n\n{$announcement->description}";

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

    public function updateImage(Request $request, Announcement $announcement, AnnouncementImage $image)
    {
        abort_unless($image->announcement_id === $announcement->id, 404);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $oldPath = $image->path;
        $image->update(['path' => $request->file('image')->store('announcements', 'public')]);

        if ($oldPath && $oldPath !== $image->path) {
            Storage::disk('public')->delete($oldPath);
        }

        $this->logAdminActivity('announcement.image_changed', "Picture changed for announcement \"{$announcement->title}\".");

        return redirect()->route('announcements')->with('success', 'Announcement picture updated.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->load('images');
        $facebookError = $this->deleteFromFacebookPage($announcement);

        if ($facebookError) {
            return redirect()->route('announcements')
                ->with('error', "Announcement was not deleted because the Facebook post could not be removed: {$facebookError}");
        }

        foreach ($announcement->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $announcement->images()->delete();
        $announcement->forceDelete();

        return redirect()->route('announcements')->with('success', 'Announcement deleted.');
    }

    private function deleteFromFacebookPage(Announcement $announcement)
    {
        $postId = $announcement->facebook_post_id;
        $accessToken = config('services.facebook.access_token');

        if (!$postId) {
            return null;
        }

        if (!$accessToken) {
            return 'Facebook credentials are not configured.';
        }

        try {
            $response = Http::timeout(20)->delete(
                "https://graph.facebook.com/{$postId}?" . http_build_query(['access_token' => $accessToken])
            );

            // Error 100 / subcode 33 means the post no longer exists on Facebook.
            $alreadyGone = $response->json('error.code') == 100 && $response->json('error.error_subcode') == 33;

            if ($response->failed() && !$alreadyGone) {
                Log::error('Facebook Delete Error: ' . $response->body());
                $this->logAdminActivity('facebook.delete_failed', 'Facebook announcement deletion failed: ' . substr($response->body(), 0, 500), 'integration');

                return $response->json('error.message') ?: 'Unexpected response from Facebook.';
            }

            $this->logAdminActivity('facebook.deleted', 'Announcement deleted from Facebook.', 'integration');

            return null;
        } catch (\Exception $e) {
            Log::error('Facebook API Exception: ' . $e->getMessage());
            $this->logAdminActivity('facebook.delete_failed', 'Facebook announcement deletion threw an exception: ' . $e->getMessage(), 'integration');

            return 'Could not reach Facebook.';
        }
    }
}