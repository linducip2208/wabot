@extends('layouts.app')
@section('title', __('campaigns.index_title'))
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('campaigns.heading') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('campaigns.subtitle') }}</p>
    </div>
    <a href="{{ route('campaigns.create') }}" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('campaigns.new_campaign') }}
    </a>
</div>

<div class="grid gap-3">
    @forelse($campaigns as $c)
    <a href="{{ route('campaigns.show', $c) }}" class="block bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center
                    {{ $c->status === 'sent' ? 'bg-emerald-50' : '' }}
                    {{ $c->status === 'sending' ? 'bg-blue-50' : '' }}
                    {{ $c->status === 'paused' ? 'bg-orange-50' : '' }}
                    {{ $c->status === 'draft' ? 'bg-amber-50' : '' }}
                    {{ $c->status === 'failed' ? 'bg-red-50' : '' }}">
                    <i class="fas {{ $c->status === 'sent' ? 'fa-check-circle text-emerald-500' : ($c->status === 'sending' ? 'fa-spinner fa-spin text-blue-500' : ($c->status === 'paused' ? 'fa-pause-circle text-orange-500' : ($c->status === 'draft' ? 'fa-clock text-amber-500' : 'fa-exclamation-circle text-red-500'))) }}"></i>
                </div>
                <div>
                    <div class="font-semibold text-gray-900">{{ $c->name }}</div>
                    <div class="text-xs text-gray-500">
                        @php
                            $channelLabel = $c->channel ?? 'whatsapp';
                            $channelBadge = [
                                'whatsapp' => 'bg-emerald-50 text-emerald-700',
                                'meta' => 'bg-blue-50 text-blue-700',
                                'telegram' => 'bg-sky-50 text-sky-700',
                            ][$channelLabel] ?? 'bg-gray-50 text-gray-700';
                            $channelName = [
                                'whatsapp' => 'WhatsApp',
                                'meta' => 'Meta Cloud',
                                'telegram' => 'Telegram',
                            ][$channelLabel] ?? 'WhatsApp';
                        @endphp
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium {{ $channelBadge }}">{{ $channelName }}</span>
                        · {{ $c->session?->name ?? $c->metaAccount?->name ?? $c->telegramAccount?->name ?? '-' }} · {{ $c->delay_min_seconds ?? $c->delay_seconds ?? 300 }}–{{ $c->delay_max_seconds ?? 400 }}s interval</div>
                </div>
            </div>
            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
                {{ $c->status === 'sent' ? 'bg-emerald-50 text-emerald-700' : '' }}
                {{ $c->status === 'sending' ? 'bg-blue-50 text-blue-700' : '' }}
                {{ $c->status === 'paused' ? 'bg-orange-50 text-orange-700' : '' }}
                {{ $c->status === 'draft' ? 'bg-amber-50 text-amber-700' : '' }}
                {{ $c->status === 'failed' ? 'bg-red-50 text-red-700' : '' }}">
                {{ ['sent' => __('common.sent'), 'sending' => __('common.sending'), 'paused' => __('campaigns.pause'), 'draft' => __('common.draft'), 'failed' => __('common.failed')][$c->status] ?? $c->status }}
            </span>
        </div>
        <p class="text-sm text-gray-500 mb-3 line-clamp-1">{{ Str::limit($c->message, 100) }}</p>
        <div class="flex items-center gap-4 text-xs text-gray-400">
            <span><i class="fas fa-users mr-1"></i> {{ $c->sent_count }}/{{ $c->total_recipients }}</span>
            @if($c->failed_count) <span class="text-red-500"><i class="fas fa-times mr-1"></i> {{ $c->failed_count }} {{ __('common.failed') }}</span> @endif
            <span>{{ $c->scheduled_at ? __('campaigns.scheduled', ['datetime' => $c->scheduled_at->format('d M H:i')]) : $c->created_at->format('d M Y H:i') }}</span>
            <div class="ml-auto flex gap-1" onclick="event.preventDefault(); event.stopPropagation();">
                @if(in_array($c->status, ['sent','failed']))
                <form method="POST" action="{{ route('campaigns.resend', $c) }}" class="inline">
                    @csrf
                    <button class="text-[11px] bg-amber-50 text-amber-700 hover:bg-amber-100 px-2 py-1 rounded-lg font-medium">{{ __('campaigns.resend') }}</button>
                </form>
                @endif
                @if($c->status === 'sending')
                <form method="POST" action="{{ route('campaigns.pause', $c) }}" class="inline">
                    @csrf
                    <button class="text-[11px] bg-orange-50 text-orange-700 hover:bg-orange-100 px-2 py-1 rounded-lg font-medium">{{ __('campaigns.pause') }}</button>
                </form>
                @endif
                @if($c->status === 'paused')
                <form method="POST" action="{{ route('campaigns.resume', $c) }}" class="inline">
                    @csrf
                    <button class="text-[11px] bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-2 py-1 rounded-lg font-medium">{{ __('campaigns.resume') }}</button>
                </form>
                @endif
            </div>
        </div>
    </a>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <i class="fas fa-bullhorn text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500 mb-1">{{ __('campaigns.empty_title') }}</p>
        <p class="text-sm text-gray-400">{{ __('campaigns.empty_subtitle') }}</p>
        <a href="{{ route('campaigns.create') }}" class="inline-block mt-4 bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">{{ __('campaigns.empty_cta') }}</a>
    </div>
    @endforelse
</div>
@endsection
