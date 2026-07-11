@extends('layouts.app')
@section('title', __('campaigns.create_title'))
@section('content')

<div x-data="campaignWizard()" class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('campaigns.index') }}" class="text-sm text-gray-500 hover:text-brand-600">
            <i class="fas fa-arrow-left mr-1"></i> {{ __('common.back') }}
        </a>
    </div>

    {{-- Stepper --}}
    <div class="flex items-center justify-center mb-8">
        <template x-for="(step, i) in steps" :key="i">
            <div class="flex items-center">
                <div class="flex items-center gap-2" :class="i > 0 && 'ml-2'">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition"
                        :class="current >= i ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-500'">
                        <span x-show="current > i"><i class="fas fa-check text-[10px]"></i></span>
                        <span x-show="current <= i" x-text="i+1"></span>
                    </div>
                    <span class="text-sm font-medium hidden sm:inline" :class="current >= i ? 'text-brand-700' : 'text-gray-400'" x-text="step"></span>
                </div>
                <div class="w-8 sm:w-16 h-px mx-1 sm:mx-2" :class="i < 2 ? (current > i ? 'bg-brand-400' : 'bg-gray-200') : 'hidden'"></div>
            </div>
        </template>
    </div>

    <form method="POST" action="{{ route('campaigns.store') }}" @submit="submitting = true" novalidate class="bg-white rounded-2xl border border-gray-200 shadow-sm">
        @csrf

        @if ($errors->any())
        <div class="mx-6 mt-5 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Step 1: Audience --}}
        <div x-show="current === 0" class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">{{ __('common.select') }} {{ __('campaigns.step_audience') }}</h2>
            <p class="text-sm text-gray-500 mb-5">{{ __('campaigns.determine_session') }}</p>

            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('campaigns.channel') }}</label>
                    <select name="channel" x-model="channel" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        <option value="whatsapp">WhatsApp (Baileys)</option>
                        <option value="meta">WhatsApp Cloud (Meta)</option>
                        <option value="telegram">Telegram</option>
                        <option value="instagram">Instagram</option>
                        <option value="facebook">Facebook</option>
                        <option value="gbm">Google Business Messages</option>
                        <option value="discord">Discord</option>
                        <option value="tiktok">TikTok</option>
                        <option value="line">LINE</option>
                        <option value="twitter">X / Twitter</option>
                        <option value="sms">SMS (Twilio)</option>
                        <option value="email">Email (SendGrid)</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('campaigns.campaign_name') }}</label>
                    <input type="text" name="name" x-model="campaignName" placeholder="Promo Lebaran 2026" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>

            {{-- WhatsApp session selector --}}
            <div x-show="channel === 'whatsapp'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">{{ __('common.session') }} WhatsApp</label>
                <select name="session_id" x-model="sessionId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">{{ __('common.select') }} {{ __('common.session') }}</option>
                    @foreach($sessions as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->phone ?? 'offline' }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Meta account selector --}}
            <div x-show="channel === 'meta'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">Meta Account</label>
                <select name="meta_account_id" x-model="metaAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih Meta Account</option>
                    @foreach($metaAccounts as $m)
                        <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->phone_number ?? '-' }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Telegram account selector --}}
            <div x-show="channel === 'telegram'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">Telegram Account</label>
                <select name="telegram_account_id" x-model="telegramAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih Telegram Account</option>
                    @foreach($telegramAccounts as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} (@{{ $t->bot_username }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Instagram account selector --}}
            <div x-show="channel === 'instagram'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">Instagram Account</label>
                <select name="instagram_account_id" x-model="instagramAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih Instagram Account</option>
                    @foreach($instagramAccounts as $ig)
                        <option value="{{ $ig->id }}">{{ $ig->name }} ({{ $ig->instagram_id }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Facebook account selector --}}
            <div x-show="channel === 'facebook'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">Facebook Account</label>
                <select name="facebook_account_id" x-model="facebookAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih Facebook Account</option>
                    @foreach($facebookAccounts as $fb)
                        <option value="{{ $fb->id }}">{{ $fb->name }} ({{ $fb->page_id }})</option>
                    @endforeach
                </select>
            </div>

            {{-- GBM account selector --}}
            <div x-show="channel === 'gbm'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">GBM Account</label>
                <select name="gbm_account_id" x-model="gbmAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih GBM Account</option>
                    @foreach($gbmAccounts as $g)
                        <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->brand_id }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Discord account selector --}}
            <div x-show="channel === 'discord'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">Discord Account</label>
                <select name="discord_account_id" x-model="discordAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih Discord Account</option>
                    @foreach($discordAccounts as $d)
                        <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->bot_name }})</option>
                    @endforeach
                </select>
            </div>

            {{-- TikTok account selector --}}
            <div x-show="channel === 'tiktok'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">TikTok Account</label>
                <select name="tiktok_account_id" x-model="tiktokAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih TikTok Account</option>
                    @foreach($tiktokAccounts as $tt)
                        <option value="{{ $tt->id }}">{{ $tt->name }} ({{ $tt->open_id }})</option>
                    @endforeach
                </select>
            </div>

            {{-- LINE account selector --}}
            <div x-show="channel === 'line'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">LINE Account</label>
                <select name="line_account_id" x-model="lineAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih LINE Account</option>
                    @foreach($lineAccounts as $l)
                        <option value="{{ $l->id }}">{{ $l->name }} ({{ $l->channel_id }})</option>
                    @endforeach
                </select>
            </div>

            {{-- X/Twitter account selector --}}
            <div x-show="channel === 'twitter'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">X / Twitter Account</label>
                <select name="twitter_account_id" x-model="twitterAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih X/Twitter Account</option>
                    @foreach($twitterAccounts as $tw)
                        <option value="{{ $tw->id }}">{{ $tw->name }} (@{{ $tw->username }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Twilio account selector --}}
            <div x-show="channel === 'sms'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">SMS (Twilio) Account</label>
                <select name="twilio_account_id" x-model="twilioAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih Twilio Account</option>
                    @foreach($twilioAccounts as $sms)
                        <option value="{{ $sms->id }}">{{ $sms->name }} ({{ $sms->phone_number }})</option>
                    @endforeach
                </select>
            </div>

            {{-- SendGrid account selector --}}
            <div x-show="channel === 'email'" class="mb-5">
                <label class="text-xs font-medium text-gray-500">Email (SendGrid) Account</label>
                <select name="sendgrid_account_id" x-model="sendgridAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih SendGrid Account</option>
                    @foreach($sendgridAccounts as $sg)
                        <option value="{{ $sg->id }}">{{ $sg->name }} ({{ $sg->from_email }})</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 mb-3">
                <span class="text-xs font-medium text-gray-500">{{ __('common.select') }} {{ __('common.contact') }}</span>
                <span class="text-[11px] text-gray-400" x-text="selectedCount + ' {{ __('campaigns.of_selected') }}'.replace(':selected', selectedCount).replace(':total', '{{ $contacts->count() }}')"></span>
                <button type="button" @click="selectAll()" class="text-[11px] text-brand-600 hover:underline ml-auto">{{ __('common.select') }} {{ __('common.all') }}</button>
                <button type="button" @click="deselectAll()" class="text-[11px] text-gray-500 hover:underline">{{ __('common.delete') }}</button>
            </div>

            <div class="border border-gray-200 rounded-xl max-h-48 overflow-y-auto divide-y divide-gray-100 mb-4">
                @forelse($contacts as $c)
                <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" name="recipient_ids[]" value="{{ $c->id }}" x-model="selectedIds" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900">{{ $c->name !== $c->phone ? $c->name : preg_replace('/@.*$/', '', $c->phone) }}</div>
                        <div class="text-xs text-gray-500">{{ preg_replace('/@.*$/', '', $c->phone) }}</div>
                    </div>
                </label>
                @empty
                <p class="px-4 py-8 text-center text-sm text-gray-500">{{ __('campaigns.empty_title') }}. <a href="{{ route('contacts.index') }}" class="text-brand-600 hover:underline">{{ __('common.create') }} {{ __('common.contact') }}</a></p>
                @endforelse
            </div>

            <div class="border-t border-gray-100 pt-4">
                <button type="button" @click="manualTab = !manualTab" class="text-xs font-medium text-brand-600 hover:underline flex items-center gap-1">
                    <i class="fas fa-plus-circle text-[10px]"></i> <span x-text="manualTab ? '{{ __('campaigns.hide_manual') }}' : '{{ __('campaigns.create_manual') }}'"></span>
                </button>
                <div x-show="manualTab" class="mt-3">
                    <label class="text-xs font-medium text-gray-500">{{ __('campaigns.manual_numbers_label') }}</label>
                    <textarea name="manual_numbers" x-model="manualNumbers" rows="5" placeholder="6281234567890&#10;Budi,6289876543210&#10;6281111111111" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ __('common.total') }}: <span x-text="selectedCount"></span> {{ __('common.receiver') }}</p>
                </div>
            </div>
        </div>

        {{-- Step 2: Message --}}
        <div x-show="current === 1" class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">{{ __('campaigns.write_message') }}</h2>
            <p class="text-sm text-gray-500 mb-5">{{ __('campaigns.spintax_hint') }}</p>

            <div class="mb-4">
                <label class="text-xs font-medium text-gray-500">{{ __('common.message') }} <span class="text-gray-400">({'{Halo|Hai|Pagi}'} = spintax, {'{name}'}, {'{phone}'})</span></label>
                <textarea name="message" x-model="messageText" rows="5" required placeholder="Halo {'{name}'}! {'{Kami ada promo spesial|Jangan lewatkan diskon 50%|Ada penawaran menarik untuk Anda}'}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('campaigns.delay_between') }}</label>
                    <input type="number" name="delay_seconds" x-model="delaySeconds" min="1" max="60" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ __('campaigns.delay_recommendation') }}</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('campaigns.media_url_optional') }}</label>
                    <input type="url" name="media_url" placeholder="https://example.com/image.jpg" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>

            {{-- Live Preview --}}
            <div class="bg-[#efeae2] rounded-xl p-4">
                <div class="text-[10px] text-gray-500 mb-2 font-medium uppercase tracking-wide">{{ __('common.preview') }} {{ __('common.message') }}</div>
                <div class="bg-[#d9fdd3] rounded-lg rounded-br-none px-3.5 py-2 text-sm shadow-sm inline-block max-w-[80%]" x-text="previewMessage() || '{{ __('campaigns.message_placeholder') }}'"></div>
                <div class="text-[10px] text-gray-400 mt-1"><span x-text="selectedCount"></span> {{ __('common.receiver') }}</div>
            </div>
        </div>

        {{-- Step 3: Review & Launch --}}
        <div x-show="current === 2" class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">{{ __('campaigns.review_and_send') }}</h2>
            <p class="text-sm text-gray-500 mb-5">{{ __('campaigns.review_subtitle') }}</p>

            <div class="bg-gray-50 rounded-xl p-4 space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">{{ __('campaigns.campaign') }}</span><span class="font-medium" x-text="campaignName || '-'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('common.receiver') }}</span><span class="font-medium" x-text="selectedCount + ' {{ __('common.contact') }}'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('campaigns.delay') }}</span><span class="font-medium" x-text="delaySeconds + ' {{ __('common.second') }}'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">{{ __('campaigns.estimated') }} {{ __('common.completed') }}</span><span class="font-medium" x-text="estimateFinish()"></span></div>
                <div class="border-t border-gray-200 pt-3 mt-3">
                    <div class="text-xs text-gray-500 mb-1">{{ __('common.message') }}:</div>
                    <div class="bg-white rounded-lg p-3 text-sm whitespace-pre-wrap" x-text="messageText || '-'"></div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50/50 rounded-b-2xl">
            <button type="button" @click="prev()" x-show="current > 0"
                class="bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-1"></i> {{ __('campaigns.previous') }}
            </button>
            <div x-show="current === 0"></div>
            <button type="button" @click="next()" x-show="current < 2"
                class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition ml-auto">
                {{ __('campaigns.next') }} <i class="fas fa-arrow-right ml-1"></i>
            </button>
            <button type="submit" x-show="current === 2" :disabled="submitting || selectedCount === 0"
                class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-700 disabled:bg-gray-300 transition ml-auto">
                <span x-show="!submitting"><i class="fas fa-paper-plane mr-1"></i> {{ __('campaigns.send_campaign') }}</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-1"></i> {{ __('campaigns.sending') }}</span>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('campaignWizard', () => ({
        current: 0,
        steps: ['{{ __('campaigns.step_audience') }}', '{{ __('common.message') }}', '{{ __('common.send') }}'],
        selectedIds: [],
        manualNumbers: '',
        manualTab: false,
        messageText: '',
        delaySeconds: 3,
        channel: 'whatsapp',
        sessionId: '',
        metaAccountId: '',
        telegramAccountId: '',
        instagramAccountId: '',
        facebookAccountId: '',
        gbmAccountId: '',
        discordAccountId: '',
        tiktokAccountId: '',
        lineAccountId: '',
        twitterAccountId: '',
        twilioAccountId: '',
        sendgridAccountId: '',
        campaignName: '',
        submitting: false,

        get totalContacts() { return {{ $contacts->count() }}; },
        get selectedCount() { return this.selectedIds.length + this.parseManualCount(); },

        selectAll() { this.selectedIds = Array.from(document.querySelectorAll('input[name="recipient_ids[]"]')).map(el => el.value); },
        deselectAll() { this.selectedIds = []; },

        parseManualCount() {
            if (!this.manualNumbers.trim()) return 0;
            return this.manualNumbers.trim().split('\n').filter(l => l.trim()).length;
        },

        next() {
            if (this.current === 0) {
                if (this.channel === 'whatsapp' && !this.sessionId) {
                    alert('{{ __('campaigns.alert_select_session') }}');
                    return;
                }
                if (this.channel === 'meta' && !this.metaAccountId) {
                    alert('{{ __('campaigns.alert_select_meta') }}');
                    return;
                }
                if (this.channel === 'telegram' && !this.telegramAccountId) {
                    alert('{{ __('campaigns.alert_select_telegram') }}');
                    return;
                }
                if (this.channel === 'instagram' && !this.instagramAccountId) {
                    alert('Pilih Instagram Account terlebih dahulu');
                    return;
                }
                if (this.channel === 'facebook' && !this.facebookAccountId) {
                    alert('Pilih Facebook Account terlebih dahulu');
                    return;
                }
                if (this.channel === 'gbm' && !this.gbmAccountId) {
                    alert('Pilih GBM Account terlebih dahulu');
                    return;
                }
                if (this.channel === 'discord' && !this.discordAccountId) {
                    alert('Pilih Discord Account terlebih dahulu');
                    return;
                }
                if (this.channel === 'tiktok' && !this.tiktokAccountId) {
                    alert('Pilih TikTok Account terlebih dahulu');
                    return;
                }
                if (this.channel === 'line' && !this.lineAccountId) {
                    alert('Pilih LINE Account terlebih dahulu');
                    return;
                }
                if (this.channel === 'twitter' && !this.twitterAccountId) {
                    alert('Pilih X/Twitter Account terlebih dahulu');
                    return;
                }
                if (this.channel === 'sms' && !this.twilioAccountId) {
                    alert('Pilih Twilio Account terlebih dahulu');
                    return;
                }
                if (this.channel === 'email' && !this.sendgridAccountId) {
                    alert('Pilih SendGrid Account terlebih dahulu');
                    return;
                }
                if (!this.campaignName.trim()) {
                    alert('{{ __('campaigns.alert_enter_name') }}');
                    return;
                }
                if (this.selectedCount === 0) {
                    alert('{{ __('campaigns.alert_select_contact') }}');
                    return;
                }
            }
            if (this.current < 2) this.current++;
        },
        prev() { if (this.current > 0) this.current--; },

        previewMessage() {
            let msg = this.messageText || '';
            msg = msg.replace(/\{[^}]+\}/g, m => {
                const opts = m.slice(1, -1).split('|');
                return opts[0];
            });
            return msg || null;
        },

        estimateFinish() {
            const msgs = this.selectedCount;
            const delay = parseInt(this.delaySeconds) || 3;
            const totalSeconds = msgs * (delay + 0.5);
            const mins = Math.ceil(totalSeconds / 60);
            if (mins < 1) return '{{ __('campaigns.less_than_minute') }}';
            if (mins < 60) return '{{ __('campaigns.est_minutes', ['mins' => '__MINS__']) }}'.replace('__MINS__', mins);
            return '{{ __('campaigns.est_hours', ['hours' => '__HOURS__', 'mins' => '__MINS__']) }}'.replace('__HOURS__', Math.ceil(mins / 60)).replace('__MINS__', mins % 60);
        }
    }));
});
</script>
@endsection
