@extends('layouts.app')
@section('title', __('common.session') . ' WhatsApp — WABot')
@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold text-gray-900">{{ __('common.session') }} WhatsApp</h1>
    <div class="flex gap-2">
        <a href="{{ route('sessions.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            Refresh {{ __('common.status') }}
        </a>
        <button onclick="document.getElementById('createSessionModal').classList.remove('hidden')"
            class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
            + {{ __('common.create') }} {{ __('common.session') }}
        </button>
    </div>
</div>

<div class="grid gap-4">
    @forelse($sessions as $s)
    <a href="{{ route('sessions.show', $s) }}"
        class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-brand-300 hover:shadow-sm transition">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <span class="inline-block w-3 h-3 rounded-full
                        {{ $s->status === 'connected' ? 'bg-green-500 animate-pulse' : '' }}
                        {{ $s->status === 'qr_ready' ? 'bg-yellow-500' : '' }}
                        {{ $s->status === 'connecting' ? 'bg-blue-500 animate-pulse' : '' }}
                        {{ $s->status === 'disconnected' ? 'bg-red-500' : '' }}
                        {{ $s->status === 'reconnecting' ? 'bg-orange-500 animate-pulse' : '' }}
                        {{ $s->status === 'pending' ? 'bg-gray-400' : '' }}">
                    </span>
                </div>
                <div>
                    <div class="font-semibold text-gray-900">{{ $s->name }}</div>
                    <div class="text-sm text-gray-500">{{ $s->phone ?? __('sessions.not_connected') }}</div>
                    @if($s->server)
                    <div class="text-xs text-gray-400 mt-0.5">{{ $s->server->name }} ({{ $s->server->host }})</div>
                    @endif
                </div>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                {{ $s->status === 'connected' ? 'bg-green-100 text-green-800' : '' }}
                {{ $s->status === 'qr_ready' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $s->status === 'connecting' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $s->status === 'reconnecting' ? 'bg-orange-100 text-orange-800' : '' }}
                {{ $s->status === 'disconnected' ? 'bg-red-100 text-red-800' : '' }}
                {{ $s->status === 'pending' ? 'bg-gray-100 text-gray-600' : '' }}">
                {{ str_replace('_', ' ', $s->status) }}
            </span>
        </div>
    </a>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-500 mb-2">{{ __('sessions.empty') }}</p>
        <p class="text-sm text-gray-400">{{ __('sessions.empty_hint') }}</p>
    </div>
    @endforelse
</div>

{{-- Create Modal --}}
<div id="createSessionModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h2 class="text-lg font-bold mb-4">{{ __('common.create') }} {{ __('common.session') }} WhatsApp</h2>
        <form method="POST" action="{{ route('sessions.store') }}" class="space-y-4">
            @csrf
            <select name="server_id" required class="w-full rounded-xl border-gray-300 px-4 py-2.5 border text-sm">
                <option value="">{{ __('common.select') }} {{ __('common.server') }} Baileys</option>
                @foreach($servers as $srv)
                <option value="{{ $srv->id }}">{{ $srv->name }} ({{ $srv->host }}:{{ $srv->port }})</option>
                @endforeach
            </select>
            <input type="text" name="name" placeholder="{{ __('sessions.name_placeholder') }}" required
                class="w-full rounded-xl border-gray-300 px-4 py-2.5 border text-sm">
            <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">
                {{ __('sessions.create_session') }}
            </button>
        </form>
    </div>
</div>
@endsection
