@extends('layouts.app')
@section('title', __('line.title') . ' — WABot')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">{{ __('line.title') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('line.subtitle') }}</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
            class="bg-green-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-green-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> {{ __('line.add_channel') }}
        </button>
    </div>

    @if($accounts->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-green-500 rounded-full flex items-center justify-center mb-4">
                <i class="fab fa-line text-white text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1">{{ __('line.empty_title') }}</h3>
            <p class="text-sm text-gray-400 mb-4">{{ __('line.empty_desc') }}</p>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="bg-green-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-green-700 transition">
                {{ __('line.add_channel') }}
            </button>
        </div>
    @else
        <div class="space-y-3">
            @foreach($accounts as $acc)
                <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                                <i class="fab fa-line text-white text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold text-gray-900">{{ $acc->name }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $acc->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $acc->is_active ? __('common.connected') : __('common.disconnected') }}
                                    </span>
                                </div>
                                @if($acc->display_name)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $acc->display_name }}</p>
                                @endif
                                @if($acc->channel_id)
                                    <p class="text-[10px] text-gray-400 font-mono">Channel ID: {{ $acc->channel_id }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            @if(!$acc->is_active)
                                <form action="{{ route('line.connect', $acc) }}" method="POST">
                                    @csrf
                                    <button class="px-3.5 py-2 rounded-xl text-xs font-medium bg-green-600 text-white hover:bg-green-700 transition">
                                        <i class="fab fa-line mr-1"></i>{{ __('common.connect') }}
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('line.disconnect', $acc) }}" method="POST">
                                    @csrf
                                    <button class="px-3.5 py-2 rounded-xl text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 transition">
                                        {{ __('common.disconnect') }}
                                    </button>
                                </form>
                                <button onclick="openTestModal({{ $acc->id }})"
                                    class="px-3 py-2 rounded-xl text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                    <i class="fas fa-paper-plane mr-1"></i>{{ __('common.test') }}
                                </button>
                            @endif
                            <form action="{{ route('line.destroy', $acc) }}" method="POST" onsubmit="return confirm('{{ __('common.delete') }}?')" class="inline">
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

    <div class="mt-4 bg-green-50 rounded-xl border border-green-100 p-5">
        <div class="flex items-start gap-4">
            <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-info text-green-600 text-sm"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1">{{ __('line.setup_title') }}</h3>
                <p class="text-xs text-gray-500 mb-2">{!! __('line.setup_desc') !!}</p>
                <code class="inline-block bg-white border border-green-200 rounded-lg px-3 py-1.5 text-xs font-mono text-green-700 break-all">
                    {{ route('webhook.line') }}
                </code>
            </div>
        </div>
    </div>
</div>

{{-- ADD MODAL --}}
<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('line.add_channel') }}</h2>
        <form action="{{ route('line.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.name') }}</label>
                <input type="text" name="name" placeholder="LINE Official Account" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('line.channel_id') }}</label>
                <input type="text" name="channel_id" placeholder="From LINE Developers Console" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('line.channel_secret') }}</label>
                <input type="text" name="channel_secret" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('line.channel_access_token') }}</label>
                <input type="text" name="channel_access_token" placeholder="Long-lived access token" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-green-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-green-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- TEST MODALS --}}
@foreach($accounts as $acc)
<div id="testModal{{ $acc->id }}" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('common.test') }} {{ __('common.send') }}: {{ $acc->name }}</h2>
        <form action="{{ route('line.test', $acc) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('line.user_id') }}</label>
                <input type="text" name="user_id" placeholder="Uxxxxxxxx" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.message') }}</label>
                <textarea name="message" rows="3" placeholder="Test message..." required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('testModal{{ $acc->id }}').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-green-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-green-700">{{ __('common.send') }}</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
function openTestModal(id) { document.getElementById('testModal' + id).classList.remove('hidden'); }
</script>
@endpush
