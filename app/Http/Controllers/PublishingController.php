<?php

namespace App\Http\Controllers;

use App\Models\WaPost;
use App\Models\WaPostCampaign;
use App\Models\WaPostLabel;
use App\Models\WaCaptionLibrary;
use App\Models\WaFacebookAccount;
use App\Models\WaInstagramAccount;
use App\Models\WaRssSchedule;
use App\Models\WaTiktokAccount;
use App\Models\WaTwitterAccount;
use App\Services\SocialPublishingService;
use Illuminate\Http\Request;

class PublishingController extends Controller
{
    public function index()
    {
        $facebookAccounts = WaFacebookAccount::where('user_id', auth()->id())->where('is_active', true)->get();
        $instagramAccounts = WaInstagramAccount::where('user_id', auth()->id())->where('is_active', true)->get();
        $twitterAccounts = WaTwitterAccount::where('user_id', auth()->id())->where('is_active', true)->get();
        $tiktokAccounts = WaTiktokAccount::where('user_id', auth()->id())->where('is_active', true)->get();
        $accountsCount = $facebookAccounts->count() + $instagramAccounts->count() + $twitterAccounts->count() + $tiktokAccounts->count();

        $campaigns = WaPostCampaign::where('user_id', auth()->id())->get();
        $labels = WaPostLabel::where('user_id', auth()->id())->get();
        $captions = WaCaptionLibrary::where('user_id', auth()->id())->get();
        $recentPosts = WaPost::where('user_id', auth()->id())->latest()->take(10)->get();

        return view('publishing.index', compact(
            'facebookAccounts', 'instagramAccounts', 'twitterAccounts', 'tiktokAccounts',
            'campaigns', 'labels', 'captions', 'recentPosts', 'accountsCount'
        ));
    }

    public function calendar(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        $posts = WaPost::where('user_id', auth()->id())
            ->whereNotNull('scheduled_at')
            ->whereYear('scheduled_at', $year)
            ->whereMonth('scheduled_at', $month)
            ->get()
            ->groupBy(function ($post) {
                return $post->scheduled_at->format('Y-m-d');
            });

        $scheduledCount = WaPost::where('user_id', auth()->id())->scheduled()->count();
        $publishedCount = WaPost::where('user_id', auth()->id())->published()->count();

        return view('publishing.calendar', compact('posts', 'year', 'month', 'scheduledCount', 'publishedCount'));
    }

    public function queue()
    {
        $posts = WaPost::where('user_id', auth()->id())
            ->scheduled()
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->paginate(20);

        return view('publishing.queue', compact('posts'));
    }

    public function drafts()
    {
        $posts = WaPost::where('user_id', auth()->id())
            ->draft()
            ->latest()
            ->paginate(20);

        return view('publishing.drafts', compact('posts'));
    }

    public function store(Request $request, SocialPublishingService $service)
    {
        $validated = $request->validate([
            'content' => 'nullable|string',
            'media_urls' => 'nullable|array',
            'media_urls.*' => 'url',
            'platform_targets' => 'required|array',
            'platform_targets.*' => 'string',
            'scheduled_at' => 'nullable|date',
            'campaign_id' => 'nullable|exists:wa_post_campaigns,id',
            'label_id' => 'nullable|exists:wa_post_labels,id',
            'action' => 'required|in:draft,schedule,publish',
        ]);

        $data = [
            'content' => $validated['content'] ?? null,
            'media_urls' => $validated['media_urls'] ?? null,
            'platform_targets' => $validated['platform_targets'],
            'campaign_id' => $validated['campaign_id'] ?? null,
            'label_id' => $validated['label_id'] ?? null,
        ];

        if ($validated['action'] === 'publish') {
            $data['scheduled_at'] = now();
        } else {
            $data['scheduled_at'] = $validated['scheduled_at'] ?? null;
        }

        $post = $service->createPost($data, auth()->id());

        if ($validated['action'] === 'publish') {
            $service->publishNow($post);
            return back()->with('success', __('messages.success.post_published'));
        }

        if ($validated['action'] === 'schedule') {
            return back()->with('success', __('messages.success.post_scheduled', ['date' => $post->scheduled_at->format('d M Y H:i')]));
        }

        return back()->with('success', __('messages.success.post_drafted'));
    }

    public function publish(WaPost $post, SocialPublishingService $service)
    {
        if ($post->user_id !== auth()->id()) {
            abort(403);
        }

        $service->publishNow($post);
        return back()->with('success', __('messages.success.post_publishing'));
    }

    public function destroy(WaPost $post)
    {
        if ($post->user_id !== auth()->id()) {
            abort(403);
        }

        if ($post->isPublished()) {
            return back()->with('error', __('messages.error.cannot_delete_published'));
        }

        $post->delete();
        return back()->with('success', __('messages.success.post_deleted'));
    }

    public function campaigns(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'color' => 'nullable|string|max:20',
            ]);

            WaPostCampaign::create([
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'] ?? '#3b82f6',
            ]);

            return back()->with('success', __('messages.success.campaign_created'));
        }

        $campaigns = WaPostCampaign::where('user_id', auth()->id())
            ->withCount('posts')
            ->get();

        return view('publishing.campaigns', compact('campaigns'));
    }

    public function updateCampaign(Request $request, WaPostCampaign $campaign)
    {
        if ($campaign->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
        ]);

        $campaign->update($validated);

        return back()->with('success', __('messages.success.campaign_updated'));
    }

    public function destroyCampaign(WaPostCampaign $campaign)
    {
        if ($campaign->user_id !== auth()->id()) {
            abort(403);
        }

        $campaign->delete();
        return back()->with('success', __('messages.success.campaign_deleted'));
    }

    public function labels(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'color' => 'nullable|string|max:20',
            ]);

            WaPostLabel::create([
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'color' => $validated['color'] ?? '#3b82f6',
            ]);

            return back()->with('success', __('messages.success.label_created'));
        }

        $labels = WaPostLabel::where('user_id', auth()->id())
            ->withCount('posts')
            ->get();

        return view('publishing.labels', compact('labels'));
    }

    public function updateLabel(Request $request, WaPostLabel $label)
    {
        if ($label->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $label->update($validated);

        return back()->with('success', __('messages.success.label_updated'));
    }

    public function destroyLabel(WaPostLabel $label)
    {
        if ($label->user_id !== auth()->id()) {
            abort(403);
        }

        $label->delete();
        return back()->with('success', __('messages.success.label_deleted'));
    }

    public function captions(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'content' => 'required|string',
                'tags' => 'nullable|array',
                'tags.*' => 'string',
            ]);

            WaCaptionLibrary::create([
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'content' => $validated['content'],
                'tags' => $validated['tags'] ?? null,
            ]);

            return back()->with('success', __('messages.success.caption_saved'));
        }

        $captions = WaCaptionLibrary::where('user_id', auth()->id())->latest()->get();
        return view('publishing.captions', compact('captions'));
    }

    public function updateCaption(Request $request, WaCaptionLibrary $caption)
    {
        if ($caption->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        $caption->update($validated);

        return back()->with('success', __('messages.success.caption_updated'));
    }

    public function destroyCaption(WaCaptionLibrary $caption)
    {
        if ($caption->user_id !== auth()->id()) {
            abort(403);
        }

        $caption->delete();
        return back()->with('success', __('messages.success.caption_deleted'));
    }

    public function rssSchedules(Request $request)
    {
        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'feed_url' => 'required|url',
                'platform_targets' => 'required|array',
                'platform_targets.*' => 'string',
                'interval_minutes' => 'nullable|integer|min:5',
            ]);

            WaRssSchedule::create([
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'feed_url' => $validated['feed_url'],
                'platform_targets' => $validated['platform_targets'],
                'interval_minutes' => $validated['interval_minutes'] ?? 60,
                'is_active' => true,
            ]);

            return back()->with('success', __('messages.success.rss_schedule_created'));
        }

        $schedules = WaRssSchedule::where('user_id', auth()->id())
            ->withCount('histories')
            ->latest()
            ->get();

        $facebookAccounts = WaFacebookAccount::where('user_id', auth()->id())->where('is_active', true)->get();
        $instagramAccounts = WaInstagramAccount::where('user_id', auth()->id())->where('is_active', true)->get();
        $twitterAccounts = WaTwitterAccount::where('user_id', auth()->id())->where('is_active', true)->get();
        $tiktokAccounts = WaTiktokAccount::where('user_id', auth()->id())->where('is_active', true)->get();

        return view('publishing.rss', compact('schedules',
            'facebookAccounts', 'instagramAccounts', 'twitterAccounts', 'tiktokAccounts'
        ));
    }

    public function updateRssSchedule(Request $request, WaRssSchedule $schedule)
    {
        if ($schedule->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'feed_url' => 'required|url',
            'platform_targets' => 'required|array',
            'platform_targets.*' => 'string',
            'interval_minutes' => 'nullable|integer|min:5',
            'is_active' => 'boolean',
        ]);

        $schedule->update($validated);

        return back()->with('success', __('messages.success.rss_schedule_updated'));
    }

    public function destroyRssSchedule(WaRssSchedule $schedule)
    {
        if ($schedule->user_id !== auth()->id()) {
            abort(403);
        }

        $schedule->delete();
        return back()->with('success', __('messages.success.rss_schedule_deleted'));
    }

    public function toggleRssSchedule(WaRssSchedule $schedule)
    {
        if ($schedule->user_id !== auth()->id()) {
            abort(403);
        }

        $schedule->update(['is_active' => !$schedule->is_active]);

        return back()->with('success', $schedule->is_active
            ? __('messages.success.rss_schedule_activated')
            : __('messages.success.rss_schedule_deactivated'));
    }
}
