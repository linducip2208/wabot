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
            'recipient_ids' => 'nullable|array|max:' . $maxRecipients,
            'recipient_ids.*' => 'exists:wa_contacts,id',
            'manual_numbers' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
        ]);

        if (empty($validated['recipient_ids']) && empty(trim($validated['manual_numbers'] ?? ''))) {
            return back()->with('error', 'Pilih minimal 1 kontak atau masukkan nomor manual.')->withInput();
        }

        $recipients = collect();

        if (!empty($validated['recipient_ids'])) {
            $recipients = WaContact::where('user_id', Auth::id())
                ->whereIn('id', $validated['recipient_ids'])
                ->get();
        }

        $manualPhones = [];
        if (!empty($validated['manual_numbers'])) {
            $lines = explode("\n", trim($validated['manual_numbers']));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                if (str_contains($line, ',')) {
                    [$name, $phone] = array_map('trim', explode(',', $line, 2));
                } else {
                    $name = $line;
                    $phone = $line;
                }
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (empty($phone)) continue;

                $contact = WaContact::firstOrCreate(
                    ['user_id' => Auth::id(), 'phone' => $phone],
                    ['name' => $name]
                );
                $recipients->push($contact);
                $manualPhones[] = (string) $contact->id;
            }
        }

        $allRecipientIds = array_merge($validated['recipient_ids'] ?? [], $manualPhones);

        $campaign = WaCampaign::create([
            'user_id' => Auth::id(),
            'session_id' => $validated['session_id'],
            'name' => $validated['name'],
            'message' => $validated['message'],
            'delay_seconds' => $validated['delay_seconds'] ?? 3,
            'media_url' => $validated['media_url'] ?? null,
            'recipient_ids' => $allRecipientIds,
            'status' => ($validated['scheduled_at'] ?? null) ? 'draft' : 'sending',
            'total_recipients' => count($allRecipientIds),
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        if (empty($validated['scheduled_at'] ?? null)) {
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

        $sent = $campaign->sent_count ?? 0;
        $failed = $campaign->failed_count ?? 0;
        $startIndex = count($phones) > 0 ? 0 : $sent + $failed;

        for ($i = $startIndex; $i < count($phones); $i++) {
            $phone = $phones[$i];

            $campaign->refresh();
            if ($campaign->status === 'paused') {
                $campaign->update(['status' => 'paused']);
                return;
            }

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

            $campaign->update([
                'sent_count' => $sent,
                'failed_count' => $failed,
            ]);

            $delay = ($campaign->delay_seconds ?? 3) * 1000000;
            usleep($delay + random_int(0, 1000000));
        }

        $campaign->update([
            'status' => 'sent',
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);
    }

    public function pause(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        if ($campaign->status === 'sending') {
            $campaign->update(['status' => 'paused']);
        }
        return back()->with('success', 'Kampanye dijeda.');
    }

    public function resume(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        if ($campaign->status === 'paused') {
            $campaign->update(['status' => 'sending']);
            $recipients = WaContact::where('user_id', Auth::id())
                ->whereIn('id', $campaign->recipient_ids ?? [])
                ->get();
            $this->sendCampaign($campaign, $recipients);
        }
        return back()->with('success', 'Kampanye dilanjutkan.');
    }

    public function resend(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        $campaign->update(['status' => 'sending', 'sent_count' => 0, 'failed_count' => 0]);
        $recipients = WaContact::where('user_id', Auth::id())
            ->whereIn('id', $campaign->recipient_ids ?? [])
            ->get();
        $this->sendCampaign($campaign, $recipients);
        return back()->with('success', 'Kampanye dikirim ulang.');
    }
}
