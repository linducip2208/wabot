@extends('layouts.app')
@section('title', $campaign->name . ' — WABot')
@section('content')

<a href="{{ route('campaigns.index') }}" class="text-sm text-gray-500 hover:text-brand-600">&larr; {{ __('common.back') }}</a>
<h1 class="text-2xl font-extrabold text-gray-900 mt-1 mb-6">{{ $campaign->name }}</h1>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-3">{{ __('common.message') }}</h3>
            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $campaign->message }}</div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-3">{{ __('common.receiver') }} ({{ $campaign->total_recipients }})</h3>
            @php $campaignGroups = $campaign->contactGroups(); @endphp
            @if($campaignGroups->isNotEmpty())
            <div class="flex flex-wrap gap-1.5 mb-3">
                @foreach($campaignGroups as $grp)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700">
                        <span class="w-2 h-2 rounded-full" style="background: {{ $grp->color ?? '#3b82f6' }}"></span>{{ $grp->name }}
                    </span>
                @endforeach
            </div>
            @endif
            <div class="max-h-64 overflow-y-auto space-y-1">
                @foreach($campaign->recipient_ids as $rid)
                    @php $c = $contacts[$rid] ?? null @endphp
                    @if($c)
                    <div class="flex items-center justify-between py-1.5 text-sm">
                        <span class="font-medium text-gray-900">{{ $c->name }}</span>
                        <span class="text-gray-400 font-mono text-xs">{{ $c->phone }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-4">{{ __('common.status') }}</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">{{ __('common.status') }}</dt>
                    <dd>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $campaign->status === 'sent' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $campaign->status === 'sending' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $campaign->status === 'paused' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $campaign->status === 'stopped' ? 'bg-slate-100 text-slate-600' : '' }}
                            {{ $campaign->status === 'draft' ? 'bg-gray-100 text-gray-600' : '' }}
                            {{ $campaign->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ['sent' => __('common.sent'), 'sending' => __('common.sending'), 'paused' => __('campaigns.paused'), 'stopped' => __('campaigns.stopped'), 'draft' => __('common.draft'), 'failed' => __('common.failed')][$campaign->status] ?? $campaign->status }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">{{ __('common.sent') }}</dt><dd class="font-semibold text-green-600">{{ $campaign->sent_count }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">{{ __('common.failed') }}</dt><dd class="font-semibold text-red-500">{{ $campaign->failed_count }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">{{ __('common.total') }}</dt><dd class="font-semibold text-gray-900">{{ $campaign->total_recipients }}</dd></div>
                @if($campaign->scheduled_at)
                <div class="flex justify-between"><dt class="text-gray-500">{{ __('campaigns.scheduled_at') }}</dt><dd class="font-semibold">{{ $campaign->scheduled_at->format('d M Y H:i') }}</dd></div>
                @endif
                @if($campaign->channel === 'whatsapp' || !$campaign->channel)
                @php $senderSessions = $campaign->sendingSessions(); @endphp
                <div class="flex justify-between">
                    <dt class="text-gray-500">{{ __('campaigns.sender_numbers') }}</dt>
                    <dd class="font-semibold text-right">
                        {{ $senderSessions->count() }}
                        @if($senderSessions->count() > 1)
                            <span class="block text-[11px] font-medium text-gray-400">{{ $campaign->session_strategy === 'random' ? 'Random' : 'Round Robin' }}</span>
                        @endif
                    </dd>
                </div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500">{{ __('common.created') }}</dt><dd class="font-semibold">{{ $campaign->created_at->format('d M Y H:i') }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-3">{{ __('campaigns.progress') }}</h3>
            @php $pct = $campaign->total_recipients > 0 ? round(($campaign->sent_count / $campaign->total_recipients) * 100) : 0 @endphp
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-brand-600 h-2.5 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <div class="text-xs text-gray-500 mt-2 font-semibold">{{ $pct }}% {{ __('common.completed') }}</div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-3">{{ __('common.actions') }}</h3>
            <div class="flex flex-wrap gap-2">
                @if(in_array($campaign->status, ['draft','stopped','paused']))
                <form method="POST" action="{{ route('campaigns.play', $campaign) }}">
                    @csrf
                    <button class="text-sm bg-emerald-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-emerald-700 transition"><i class="fas fa-play mr-1.5"></i> {{ __('campaigns.play') }}</button>
                </form>
                @endif
                @if($campaign->status === 'sending')
                <form method="POST" action="{{ route('campaigns.pause', $campaign) }}">
                    @csrf
                    <button class="text-sm bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-orange-700 transition"><i class="fas fa-pause mr-1.5"></i> {{ __('campaigns.pause') }}</button>
                </form>
                @endif
                @if(in_array($campaign->status, ['sending','paused']))
                <form method="POST" action="{{ route('campaigns.stop', $campaign) }}" onsubmit="return confirm('{{ __('campaigns.confirm_stop') }}')">
                    @csrf
                    <button class="text-sm bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-700 transition"><i class="fas fa-stop mr-1.5"></i> {{ __('campaigns.stop') }}</button>
                </form>
                @endif
                @if(in_array($campaign->status, ['sent','failed','stopped']))
                <form method="POST" action="{{ route('campaigns.resend', $campaign) }}" onsubmit="return confirm('{{ __('campaigns.confirm_resend') }}')">
                    @csrf
                    <button class="text-sm bg-amber-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-amber-700 transition"><i class="fas fa-redo mr-1.5"></i> {{ __('campaigns.resend') }}</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
