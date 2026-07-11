@extends('layouts.app')
@section('title', 'WhatsApp Calling — WABot')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">WhatsApp Calling</h1>
            <p class="text-sm text-gray-500 mt-0.5">Voice broadcast via Meta API + ElevenLabs TTS</p>
        </div>
        <button onclick="document.getElementById('createModal').classList.remove('hidden')"
            class="bg-orange-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-orange-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> {{ __('calls.create') }}
        </button>
    </div>

    @if($broadcasts->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-orange-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-phone-volume text-orange-500 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1">{{ __('calls.no_broadcasts') }}</h3>
            <p class="text-sm text-gray-400">{{ __('calls.no_broadcasts_hint') }}</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($broadcasts as $b)
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $b->name }}</h3>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $b->metaAccount?->name ?? '-' }} &middot; {{ $b->created_at->format('d/m/Y H:i') }}</p>
                            <div class="flex items-center gap-3 mt-2 flex-wrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $b->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $b->status === 'sending' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $b->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $b->status === 'draft' ? 'bg-gray-100 text-gray-600' : '' }}">
                                    {{ ucfirst($b->status) }}
                                </span>
                                <span class="text-xs text-gray-500"><i class="fas fa-phone mr-1"></i>{{ $b->called_count }}/{{ $b->total_recipients }} {{ __('common.sent') }}</span>
                                <span class="text-xs text-gray-500"><i class="fas fa-check mr-1"></i>{{ $b->answered_count }} {{ __('calls.answered') }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('calls.logs', $b) }}"
                                class="px-3 py-1.5 rounded-lg text-[11px] font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                                <i class="fas fa-list mr-1"></i>Logs
                            </a>
                            <form action="{{ route('calls.destroy', $b) }}" method="POST" onsubmit="return confirm('{{ __('common.delete') }}?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div id="createModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('calls.create') }}</h2>
        <form action="{{ route('calls.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.name') }} Broadcast</label>
                <input type="text" name="name" placeholder="Contoh: Promo Lebaran" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('calls.meta_account') }}</label>
                <select name="meta_account_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->phone_number }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.message') }} {{ __('calls.voice') }} <span class="text-gray-400">{{ __('calls.voice_hint') }}</span></label>
                <textarea name="message" rows="3" placeholder="{{ __('calls.voice_placeholder') }}" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.contact') }}</label>
                <select name="recipient_ids[]" multiple class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm min-h-[100px]">
                    @foreach(\App\Models\WaContact::where('user_id', Auth::id())->limit(100)->get() as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->display_phone ?? $c->phone }})</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">{{ __('calls.manual_numbers_hint') }}</p>
                <textarea name="manual_numbers" rows="2" placeholder="62812xxxx&#10;62813xxxx"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm mt-1"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('calls.delay_seconds') }}</label>
                <input type="number" name="delay_seconds" value="10" min="5" max="120"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-orange-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-orange-700">{{ __('calls.start') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
