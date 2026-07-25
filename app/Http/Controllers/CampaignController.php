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

        $contacts = WaContact::where('user_id', Auth::id())
            ->with(['groups:contact_groups.id', 'contactTags:wa_contact_tags.id'])
            ->get();

        $groups = \App\Models\ContactGroup::where('user_id', Auth::id())
            ->withCount('contacts')->orderBy('name')->get();

        $tags = \App\Models\WaContactTag::where('user_id', Auth::id())
            ->withCount('contacts')->orderBy('name')->get();

        $templates = \App\Models\WaMessageTemplate::where('user_id', Auth::id())
            ->orderBy('name')->get();

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
            'sessions', 'contacts', 'groups', 'tags', 'templates', 'metaAccounts', 'telegramAccounts',
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
            'media_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar|max:51200',
            'recipient_ids' => 'nullable|array|max:' . $maxRecipients,
            'recipient_ids.*' => 'exists:wa_contacts,id',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:contact_groups,id',
            'recipients_file' => 'nullable|file|mimes:csv,txt,xlsx|max:10240',
            'manual_numbers' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'session_strategy' => 'nullable|in:round_robin,random',
        ];

        $rules['session_ids'] = 'required_if:channel,whatsapp|nullable|array';
        $rules['session_ids.*'] = 'exists:wa_sessions,id';
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

        $mediaFile = null;
        if ($request->hasFile('media_file')) {
            $mediaFile = $request->file('media_file')->store('campaign-media', 'public');
        }

        $sessionIds = [];
        if ($validated['channel'] === 'whatsapp') {
            $sessionIds = WaSession::where('user_id', Auth::id())
                ->whereIn('id', $validated['session_ids'] ?? [])
                ->pluck('id')->map(fn ($i) => (int) $i)->values()->all();

            if (empty($sessionIds)) {
                return back()->with('error', __('messages.error.select_session'))->withInput();
            }
        }

        if (empty($validated['recipient_ids'])
            && empty($validated['group_ids'])
            && !$request->hasFile('recipients_file')
            && empty(trim($validated['manual_numbers'] ?? ''))) {
            return back()->with('error', __('messages.error.select_contact_or_number'))->withInput();
        }

        $recipients = collect();

        if (!empty($validated['recipient_ids'])) {
            $recipients = WaContact::where('user_id', Auth::id())
                ->whereIn('id', $validated['recipient_ids'])
                ->get();
        }

        $groupIds = [];
        if (!empty($validated['group_ids'])) {
            $groups = \App\Models\ContactGroup::where('user_id', Auth::id())
                ->whereIn('id', $validated['group_ids'])
                ->with('contacts')
                ->get();

            $groupIds = $groups->pluck('id')->map(fn ($i) => (int) $i)->values()->all();

            foreach ($groups as $group) {
                $recipients = $recipients->merge($group->contacts);
            }
        }

        if ($request->hasFile('recipients_file')) {
            $rows = app(\App\Services\SpreadsheetImportService::class)->parse($request->file('recipients_file'));

            foreach ($rows as $index => $row) {
                if (empty($row) || count(array_filter($row)) === 0) continue;

                $first = trim((string) ($row[0] ?? ''));
                $second = trim((string) ($row[1] ?? ''));

                if ($index === 0 && !preg_match('/[0-9]{6,}/', $first . $second)) continue; // skip header

                if ($second !== '' && preg_match('/[0-9]{6,}/', $second)) {
                    $name = $first !== '' ? $first : $second;
                    $phone = $second;
                } else {
                    $name = $first;
                    $phone = $first;
                }

                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($phone) < 6) continue;

                $contact = WaContact::firstOrCreate(
                    ['user_id' => Auth::id(), 'phone' => $phone],
                    ['name' => $name !== '' ? $name : $phone]
                );
                $recipients->push($contact);
            }
        }

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
            }
        }

        $allRecipientIds = $recipients->unique('id')->pluck('id')
            ->map(fn ($i) => (string) $i)->values()->all();

        if (empty($allRecipientIds)) {
            return back()->with('error', __('messages.error.select_contact_or_number'))->withInput();
        }

        if (count($allRecipientIds) > $maxRecipients) {
            return back()->with('error', __('messages.error.max_recipients_exceeded', ['max' => $maxRecipients]))->withInput();
        }

        $campaign = WaCampaign::create([
            'user_id' => Auth::id(),
            'channel' => $validated['channel'],
            'session_id' => $sessionIds[0] ?? null,
            'session_ids' => $sessionIds ?: null,
            'session_strategy' => $validated['session_strategy'] ?? 'round_robin',
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
            'media_file' => $mediaFile,
            'recipient_ids' => $allRecipientIds,
            'group_ids' => $groupIds ?: null,
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

    public function play(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);

        if (!in_array($campaign->status, ['draft', 'paused', 'stopped', 'sent', 'failed'])) {
            return back()->with('error', __('messages.error.cannot_play'));
        }

        $reset = in_array($campaign->status, ['sent', 'failed', 'draft']);
        $campaign->update([
            'status' => 'sending',
            'sent_count' => $reset ? 0 : ($campaign->sent_count ?? 0),
            'failed_count' => $reset ? 0 : ($campaign->failed_count ?? 0),
        ]);

        SendCampaignJob::dispatch($campaign->id);

        return back()->with('success', __('messages.success.campaign_played'));
    }

    public function pause(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        if ($campaign->status === 'sending') {
            $campaign->update(['status' => 'paused']);
            return back()->with('success', __('messages.success.campaign_paused'));
        }
        return back()->with('error', __('messages.error.cannot_pause'));
    }

    public function stop(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        if (in_array($campaign->status, ['sending', 'paused'])) {
            $campaign->update(['status' => 'stopped']);
            return back()->with('success', __('messages.success.campaign_stopped'));
        }
        return back()->with('error', __('messages.error.cannot_stop'));
    }

    public function resume(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);
        if ($campaign->status === 'paused') {
            $campaign->update(['status' => 'sending']);
            SendCampaignJob::dispatch($campaign->id);
            return back()->with('success', __('messages.success.campaign_resumed'));
        }
        return back()->with('error', __('messages.error.cannot_resume'));
    }

    public function resend(WaCampaign $campaign)
    {
        abort_if($campaign->user_id !== Auth::id(), 403);

        if (!in_array($campaign->status, ['sent', 'failed', 'stopped'])) {
            return back()->with('error', __('messages.error.cannot_resend'));
        }

        $campaign->update(['status' => 'sending', 'sent_count' => 0, 'failed_count' => 0]);
        SendCampaignJob::dispatch($campaign->id);
        return back()->with('success', __('messages.success.campaign_resent'));
    }
}
