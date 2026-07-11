@extends('layouts.app')
@section('title', __('instagram.title') . ' — WABot')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">{{ __('instagram.title') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('instagram.subtitle') }}</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
            class="bg-pink-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-pink-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> {{ __('instagram.create_account') }}
        </button>
    </div>

    @if($accounts->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-pink-50 rounded-full flex items-center justify-center mb-4">
                <i class="fab fa-instagram text-pink-500 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1">{{ __('instagram.empty_title') }}</h3>
            <p class="text-sm text-gray-400 mb-4">{{ __('instagram.empty_desc') }}</p>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="bg-pink-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-pink-700 transition">
                {{ __('instagram.create_account') }}
            </button>
        </div>
    @else
        <div class="space-y-3">
            @foreach($accounts as $acc)
                <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-500 to-orange-400 flex items-center justify-center flex-shrink-0">
                                <i class="fab fa-instagram text-white text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold text-gray-900">{{ $acc->name }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $acc->status === 'connected' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $acc->status === 'connected' ? __('common.connected') : __('common.disconnected') }}
                                    </span>
                                </div>
                                @if($acc->username)
                                    <p class="text-xs text-gray-400 mt-0.5">@{{ $acc->username }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            @if($acc->status !== 'connected')
                                <form action="{{ route('instagram.connect', $acc) }}" method="POST">
                                    @csrf
                                    <button class="px-3.5 py-2 rounded-xl text-xs font-medium bg-pink-600 text-white hover:bg-pink-700 transition">
                                        <i class="fab fa-instagram mr-1"></i>{{ __('common.connect') }}
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('instagram.disconnect', $acc) }}" method="POST">
                                    @csrf
                                    <button class="px-3.5 py-2 rounded-xl text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 transition">
                                        {{ __('common.disconnect') }}
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('instagram.destroy', $acc) }}" method="POST" onsubmit="return confirm('{{ __('common.delete') }}?')" class="inline">
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

    <div class="mt-4 bg-pink-50 rounded-xl border border-pink-100 p-5">
        <div class="flex items-start gap-4">
            <div class="w-9 h-9 rounded-full bg-pink-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-link text-pink-600 text-sm"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1">{{ __('instagram.webhook_url') }}</h3>
                <p class="text-xs text-gray-500 mb-2">{{ __('instagram.webhook_hint') }}</p>
                <code class="inline-block bg-white border border-pink-200 rounded-lg px-3 py-1.5 text-xs font-mono text-pink-700 break-all">
                    {{ route('webhook.instagram') }}
                </code>
            </div>
        </div>
    </div>
</div>

<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('instagram.create_account') }}</h2>
        <form action="{{ route('instagram.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.name') }}</label>
                <input type="text" name="name" placeholder="Akun IG Bisnis" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('meta.app_id') }}</label>
                <input type="text" name="app_id" placeholder="Dari Meta Developer" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('meta.app_secret') }}</label>
                <input type="text" name="app_secret" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-pink-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-pink-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
