<?php

namespace App\Http\Controllers;

use App\Jobs\SendCampaignJob;
use App\Models\WaCampaign;
use App\Models\WaContact;
use App\Models\WaMetaAccount;
use App\Models\WaSession;
use App\Models\WaTelegramAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = WaCampaign::where('user_id', Auth::id())
            ->with(['session', 'metaAccount', 'telegramAccount', 'instagramAccount', 'facebookAccount', 'gbmAccount', 'discordAccount', 'tiktokAccount', 'lineAccount', 'twitterAccount', 'twilioAccount', 'sendgridAccount'])
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

        $metaAccounts = WaMetaAccount::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        $telegramAccounts = WaTelegramAccount::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        $instagramAccounts = \App\Models\WaInstagramAccount::where('user_id', Auth::id())
            ->where('is_active', true)->get();
        $facebookAccounts = \App\Models\WaFacebookAccount::where('user_id', Auth::id())
            ->where('is_active', true)->get();
        $gbmAccounts = \App\Models\WaGbmAccount::where('user_id', Auth::id())
            ->where('is_active', true)->get();
        $discordAccounts = \App\Models\WaDiscordAccount::where('user_id', Auth::id())
            ->where('is_active', true)->get();
        $tiktokAccounts = \App\Models\WaTiktokAccount::where('user_id', Auth::id())
            ->where('is_active', true)->get();
        $lineAccounts = \App\Models\WaLineAccount::where('user_id', Auth::id())
            ->where('is_active', true)->get();
        $twitterAccounts = \App\Models\WaTwitterAccount::where('user_id', Auth::id())
            ->where('is_active', true)->get();
        $twilioAccounts = \App\Models\WaTwilioAccount::where('user_id', Auth::id())
            ->where('is_active', true)->get();
        $sendgridAccounts = \App\Models\WaSendGridAccount::where('user_id', Auth::id())
            ->where('is_active', true)->get();

        return view('campaigns.create', compact(
            'sessions', 'contacts', 'metaAccounts', 'telegramAccounts',
            'instagramAccounts', 'facebookAccounts', 'gbmAccounts', 'discordAccounts',
            'tiktokAccounts', 'lineAccounts', 'twitterAccounts', 'twilioAccounts', 'sendgridAccounts'
        ));
    }

    public function store(Request $request)
    {
        $plan = Auth::user()->plan;
        $maxRecipients = $plan?->max_campaign_recipients ?? 50;

        $rules = [
            'channel' => 'required|in:whatsapp,meta,telegram,instagram,facebook,gbm,discord,tiktok,line,twitter,sms,email',
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'delay_min_seconds' => 'nullable|integer|min:1|max:3600',
            'delay_max_seconds' => 'nullable|integer|min:1|max:3600|gte:delay_min_seconds',
            'media_url' => 'nullable|url|max:1000',
            'recipient_ids' => 'nullable|array|max:' . $maxRecipients,
            'recipient_ids.*' => 'exists:wa_contacts,id',
            'manual_numbers' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
        ];

        $rules['session_id'] = 'required_if:channel,whatsapp|nullable|exists:wa_sessions,id';
        $rules['meta_account_id'] = 'required_if:channel,meta|nullable|exists:wa_meta_accounts,id';
        $rules['telegram_account_id'] = 'required_if:channel,telegram|nullable|exists:wa_telegram_accounts,id';
        $rules['instagram_account_id'] = 'required_if:channel,instagram|nullable|exists:wa_instagram_accounts,id';
        $rules['facebook_account_id'] = 'required_if:channel,facebook|nullable|exists:wa_facebook_accounts,id';
        $rules['gbm_account_id'] = 'required_if:channel,gbm|nullable|exists:wa_gbm_accounts,id';
        $rules['discord_account_id'] = 'required_if:channel,discord|nullable|exists:wa_discord_accounts,id';
        $rules['tiktok_account_id'] = 'required_if:channel,tiktok|nullable|exists:wa_tiktok_accounts,id';
        $rules['line_account_id'] = 'required_if:channel,line|nullable|exists:wa_line_accounts,id';
        $rules['twitter_account_id'] = 'required_if:channel,twitter|nullable|exists:wa_twitter_accounts,id';
        $rules['twilio_account_id'] = 'required_if:channel,sms|nullable|exists:wa_twilio_accounts,id';
        $rules['sendgrid_account_id'] = 'required_if:channel,email|nullable|exists:wa_send_grid_accounts,id';

        $validated = $request->validate($rules);

        if (empty($validated['recipient_ids']) && empty(trim($validated['manual_numbers'] ?? ''))) {
            return back()->with('error', __('messages.error.select_contact_or_number'))->withInput();
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
            'channel' => $validated['channel'],
            'session_id' => $validated['session_id'] ?? null,
            'meta_account_id' => $validated['meta_account_id'] ?? null,
            'telegram_account_id' => $validated['telegram_account_id'] ?? null,
            'instagram_account_id' => $validated['instagram_account_id'] ?? null,
            'facebook_account_id' => $validated['facebook_account_id'] ?? null,
            'gbm_account_id' => $validated['gbm_account_id'] ?? null,
            'discord_account_id' => $validated['discord_account_id'] ?? null,
            'tiktok_account_id' => $validated['tiktok_account_id'] ?? null,
            'line_account_id' => $validated['line_account_id'] ?? null,
            'twitter_account_id' => $validated['twitter_account_id'] ?? null,
            'twilio_account_id' => $validated['twilio_account_id'] ?? null,
            'sendgrid_account_id' => $validated['sendgrid_account_id'] ?? null,
            'name' => $validated['name'],
            'message' => $validated['message'],
            'delay_seconds' => $validated['delay_min_seconds'] ?? 300,
            'delay_min_seconds' => $validated['delay_min_seconds'] ?? 300,
            'delay_max_seconds' => $validated['delay_max_seconds'] ?? 400,
            'media_url' => $validated['media_url'] ?? null,
            'recipient_ids' => $allRecipientIds,
            'status' => ($validated['scheduled_at'] ?? null) ? 'draft' : 'sending',
            'total_recipients' => count($allRecipientIds),
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        if (empty($validated['scheduled_at'] ?? null)) {
            SendCampaignJob::dispatch($campaign->id);
        }

        return redirect()->route('campaigns.index')
            ->with('success', __('messages.success.campaign_created'));
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
        return back()->with('success', __('messages.success.campaign_deleted'));
    }

    public function pause(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        if ($campaign->status === 'sending') {
            $campaign->update(['status' => 'paused']);
        }
        return back()->with('success', __('messages.success.campaign_paused'));
    }

    public function resume(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        if ($campaign->status === 'paused') {
            $campaign->update(['status' => 'sending']);
            SendCampaignJob::dispatch($campaign->id);
        }
        return back()->with('success', __('messages.success.campaign_resumed'));
    }

    public function resend(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        $campaign->update(['status' => 'sending', 'sent_count' => 0, 'failed_count' => 0]);
        SendCampaignJob::dispatch($campaign->id);
        return back()->with('success', __('messages.success.campaign_resent'));
    }
}
