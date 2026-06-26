<?php

namespace App\Http\Controllers;

use App\Models\WaCampaign;
use App\Models\WaContact;
use App\Models\WaSession;
use App\Services\BaileysService;
use App\Services\SpintaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function __construct(
        protected BaileysService $baileys,
        protected SpintaxService $spintax,
    ) {}

    public function index()
    {
        $campaigns = WaCampaign::where('user_id', Auth::id())
            ->with('session')
            ->latest()
            ->get();

        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $sessions = WaSession::where('user_id', Auth::id())
            ->where('status', 'connected')
            ->where('is_active', true)
            ->get();

        $contacts = WaContact::where('user_id', Auth::id())->get();

        return view('campaigns.create', compact('sessions', 'contacts'));
    }

    public function store(Request $request)
    {
        $plan = Auth::user()->plan;
        $maxRecipients = $plan?->max_campaign_recipients ?? 50;

        $validated = $request->validate([
            'session_id' => 'required|exists:wa_sessions,id',
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'delay_seconds' => 'nullable|integer|min:1|max:60',
            'media_url' => 'nullable|url|max:1000',
            'recipient_ids' => 'required|array|min:1|max:' . $maxRecipients,
            'recipient_ids.*' => 'exists:wa_contacts,id',
            'scheduled_at' => 'nullable|date',
        ]);

        $recipients = WaContact::where('user_id', Auth::id())
            ->whereIn('id', $validated['recipient_ids'])
            ->get();

        $campaign = WaCampaign::create([
            'user_id' => Auth::id(),
            'session_id' => $validated['session_id'],
            'name' => $validated['name'],
            'message' => $validated['message'],
            'delay_seconds' => $validated['delay_seconds'] ?? 3,
            'media_url' => $validated['media_url'] ?? null,
            'recipient_ids' => $validated['recipient_ids'],
            'status' => $validated['scheduled_at'] ? 'draft' : 'sending',
            'total_recipients' => count($validated['recipient_ids']),
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        if (!$validated['scheduled_at']) {
            $this->sendCampaign($campaign, $recipients);
        }

        return redirect()->route('campaigns.index')
            ->with('success', 'Kampanye berhasil dibuat.');
    }

    public function show(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        $contacts = WaContact::whereIn('id', $campaign->recipient_ids ?? [])->get()->keyBy('id');
        return view('campaigns.show', compact('campaign', 'contacts'));
    }

    public function destroy(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        $campaign->delete();
        return back()->with('success', 'Kampanye dihapus.');
    }

    protected function sendCampaign(WaCampaign $campaign, $recipients): void
    {
        $session = $campaign->session;
        if (!$session || !$session->server) {
            $campaign->update(['status' => 'failed']);
            return;
        }

        $phones = $recipients->pluck('phone')->toArray();
        $variables = [];
        foreach ($recipients as $r) {
            $phone = preg_replace('/@.*$/', '', $r->phone);
            $variables[$r->phone] = ['name' => $r->name, 'phone' => $phone];
        }

        $sent = 0;
        $failed = 0;

        foreach ($phones as $phone) {
            $msg = $campaign->message;
            if ($this->spintax->hasSpintax($msg) || str_contains($msg, '{name}')) {
                $msg = $this->spintax->process($msg, $variables[$phone] ?? []);
            }

            $result = $this->baileys->send($session->server, $session->session_id, $phone, $msg);
            if ($result['ok'] ?? false) {
                $sent++;
            } else {
                $failed++;
            }

            $delay = ($campaign->delay_seconds ?? 3) * 1000000;
            usleep($delay + random_int(0, 1000000));
        }

        $campaign->update([
            'status' => 'sent',
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);
    }
}
