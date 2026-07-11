<?php

namespace App\Http\Controllers;

use App\Models\WaCampaign;
use App\Models\WaContact;
use App\Models\WaMetaAccount;
use App\Models\WaSession;
use App\Models\WaTelegramAccount;
use App\Services\BaileysService;
use App\Services\DiscordService;
use App\Services\FacebookService;
use App\Services\GbmService;
use App\Services\InstagramService;
use App\Services\MetaApiService;
use App\Services\SendGridService;
use App\Services\SpintaxService;
use App\Services\TelegramService;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    public function __construct(
        protected BaileysService $baileys,
        protected SpintaxService $spintax,
        protected MetaApiService $metaApi,
        protected TelegramService $telegram,
        protected InstagramService $instagram,
        protected FacebookService $facebook,
        protected GbmService $gbm,
        protected DiscordService $discord,
        protected TwilioService $twilio,
        protected SendGridService $sendgrid,
    ) {}

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
            'delay_seconds' => 'nullable|integer|min:1|max:60',
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

    protected function sendCampaign(WaCampaign $campaign, $recipients): void
    {
        $channel = $campaign->channel ?? 'whatsapp';

        if ($channel === 'whatsapp') {
            $session = $campaign->session;
            if (!$session || !$session->server) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        if ($channel === 'meta') {
            $metaAccount = $campaign->metaAccount;
            if (!$metaAccount || !$metaAccount->is_active) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        if ($channel === 'telegram') {
            $tgAccount = $campaign->telegramAccount;
            if (!$tgAccount || !$tgAccount->is_active) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        if ($channel === 'instagram') {
            $igAccount = $campaign->instagramAccount;
            if (!$igAccount || !$igAccount->is_active) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        if ($channel === 'facebook') {
            $fbAccount = $campaign->facebookAccount;
            if (!$fbAccount || !$fbAccount->is_active) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        if ($channel === 'gbm') {
            $gbmAccount = $campaign->gbmAccount;
            if (!$gbmAccount || !$gbmAccount->is_active) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        if ($channel === 'discord') {
            $dcAccount = $campaign->discordAccount;
            if (!$dcAccount || !$dcAccount->is_active) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        if ($channel === 'tiktok') {
            $ttAccount = $campaign->tiktokAccount;
            if (!$ttAccount || !$ttAccount->is_active) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        if ($channel === 'line') {
            $lineAccount = $campaign->lineAccount;
            if (!$lineAccount || !$lineAccount->is_active) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        if ($channel === 'twitter') {
            $twAccount = $campaign->twitterAccount;
            if (!$twAccount || !$twAccount->is_active) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        if ($channel === 'sms') {
            $smsAccount = $campaign->twilioAccount;
            if (!$smsAccount || !$smsAccount->is_active) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        if ($channel === 'email') {
            $sgAccount = $campaign->sendgridAccount;
            if (!$sgAccount || !$sgAccount->is_active) {
                $campaign->update(['status' => 'failed']);
                return;
            }
        }

        $phones = $recipients->pluck('phone')->toArray();
        $variables = [];
        foreach ($recipients as $r) {
            $phone = preg_replace('/@.*$/', '', $r->phone);
            $variables[$r->phone] = ['name' => $r->name, 'phone' => $phone];
        }

        $sent = $campaign->sent_count ?? 0;
        $failed = $campaign->failed_count ?? 0;

        for ($i = 0; $i < count($phones); $i++) {
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

            $to = preg_replace('/@.*$/', '', $phone);

            $result = match ($channel) {
                'meta' => $this->sendMetaMessage($campaign->metaAccount, $to, $msg),
                'telegram' => $this->sendTelegramMessage($campaign->telegramAccount, $to, $msg),
                'instagram' => $this->sendInstagramMessage($campaign->instagramAccount, $to, $msg),
                'facebook' => $this->sendFacebookMessage($campaign->facebookAccount, $to, $msg),
                'gbm' => $this->sendGbmMessage($campaign->gbmAccount, $to, $msg),
                'discord' => $this->sendDiscordMessage($campaign->discordAccount, $to, $msg),
                'tiktok' => $this->sendTiktokMessage($campaign->tiktokAccount, $to, $msg),
                'line' => $this->sendLineMessage($campaign->lineAccount, $to, $msg),
                'twitter' => $this->sendTwitterMessage($campaign->twitterAccount, $to, $msg),
                'sms' => $this->sendSmsMessage($campaign->twilioAccount, $to, $msg),
                'email' => $this->sendEmailMessage($campaign->sendgridAccount, $to, $msg),
                default => $this->sendBaileysMessage($campaign->session, $to, $msg),
            };

            if ($result) {
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

    protected function sendBaileysMessage($session, string $to, string $message): bool
    {
        $result = $this->baileys->send($session->server, $session->session_id, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendMetaMessage($metaAccount, string $to, string $message): bool
    {
        $result = $this->metaApi->sendText($metaAccount, $to, $message);
        return !isset($result['error']);
    }

    protected function sendTelegramMessage($tgAccount, string $to, string $message): bool
    {
        $result = $this->telegram->sendMessage($tgAccount, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendInstagramMessage($igAccount, string $to, string $message): bool
    {
        $result = $this->instagram->sendDM($igAccount->instagram_id, $igAccount->access_token, $message, $to);
        return empty($result['error']);
    }

    protected function sendFacebookMessage($fbAccount, string $to, string $message): bool
    {
        $result = $this->facebook->sendMessage($fbAccount, $to, $message);
        return empty($result['error']);
    }

    protected function sendGbmMessage($gbmAccount, string $to, string $message): bool
    {
        $result = $this->gbm->sendMessage($gbmAccount, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendDiscordMessage($dcAccount, string $to, string $message): bool
    {
        $result = $this->discord->sendMessage($dcAccount, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendTiktokMessage($ttAccount, string $to, string $message): bool
    {
        $result = app(\App\Services\TikTokService::class)->sendMessage($ttAccount->access_token, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendLineMessage($lineAccount, string $to, string $message): bool
    {
        $result = app(\App\Services\LineService::class)->pushMessage($lineAccount, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendTwitterMessage($twAccount, string $to, string $message): bool
    {
        $result = app(\App\Services\TwitterService::class)->sendDM($twAccount->access_token, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendSmsMessage($smsAccount, string $to, string $message): bool
    {
        $result = $this->twilio->sendSms($smsAccount, $to, $message);
        return !($result['error'] ?? false) && empty($result['error_code']);
    }

    protected function sendEmailMessage($sgAccount, string $to, string $message): bool
    {
        $result = $this->sendgrid->sendEmail($sgAccount, $to, 'WABot Campaign', $message);
        return $result['ok'] ?? false;
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
            $recipients = WaContact::where('user_id', Auth::id())
                ->whereIn('id', $campaign->recipient_ids ?? [])
                ->get();
            $this->sendCampaign($campaign, $recipients);
        }
        return back()->with('success', __('messages.success.campaign_resumed'));
    }

    public function resend(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        $campaign->update(['status' => 'sending', 'sent_count' => 0, 'failed_count' => 0]);
        $recipients = WaContact::where('user_id', Auth::id())
            ->whereIn('id', $campaign->recipient_ids ?? [])
            ->get();
        $this->sendCampaign($campaign, $recipients);
        return back()->with('success', __('messages.success.campaign_resent'));
    }
}
