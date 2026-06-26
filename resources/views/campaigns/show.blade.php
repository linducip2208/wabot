@extends('layouts.app')
@section('title', $campaign->name . ' — WABot')
@section('content')

<a href="{{ route('campaigns.index') }}" class="text-sm text-gray-500 hover:text-brand-600">&larr; Kembali</a>
<h1 class="text-2xl font-extrabold text-gray-900 mt-1 mb-6">{{ $campaign->name }}</h1>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-3">Pesan</h3>
            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap">{{ $campaign->message }}</div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-3">Penerima ({{ $campaign->total_recipients }})</h3>
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
            <h3 class="font-bold text-gray-900 mb-4">Status</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $campaign->status === 'sent' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $campaign->status === 'sending' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $campaign->status === 'draft' ? 'bg-gray-100 text-gray-600' : '' }}
                            {{ $campaign->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $campaign->status }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500">Terkirim</dt><dd class="font-semibold text-green-600">{{ $campaign->sent_count }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Gagal</dt><dd class="font-semibold text-red-500">{{ $campaign->failed_count }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Total</dt><dd class="font-semibold text-gray-900">{{ $campaign->total_recipients }}</dd></div>
                @if($campaign->scheduled_at)
                <div class="flex justify-between"><dt class="text-gray-500">Terjadwal</dt><dd class="font-semibold">{{ $campaign->scheduled_at->format('d M Y H:i') }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-gray-500">Dibuat</dt><dd class="font-semibold">{{ $campaign->created_at->format('d M Y H:i') }}</dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-3">Progress</h3>
            @php $pct = $campaign->total_recipients > 0 ? round(($campaign->sent_count / $campaign->total_recipients) * 100) : 0 @endphp
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-brand-600 h-2.5 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <div class="text-xs text-gray-500 mt-2 font-semibold">{{ $pct }}% selesai</div>
        </div>
    </div>
</div>
@endsection
