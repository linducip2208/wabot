@extends('layouts.app')
@section('title', __('common.send') . ' ' . __('common.message') . ' — WABot')
@section('content')

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('messages.sent') }}" class="text-sm text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left mr-1"></i> {{ __('common.back') }}</a>
        <span class="text-gray-300">|</span>
        <a href="{{ route('messages.received') }}" class="text-xs text-gray-500 hover:text-brand-600 {{ request()->is('messages/received') ? 'font-semibold text-brand-600' : '' }}">{{ __('messages.inbox') }}</a>
        <a href="{{ route('messages.sent') }}" class="text-xs text-gray-500 hover:text-brand-600 {{ request()->is('messages/sent') ? 'font-semibold text-brand-600' : '' }}">{{ __('common.sent') }}</a>
        <a href="{{ route('messages.queue') }}" class="text-xs text-gray-500 hover:text-brand-600 {{ request()->is('messages/queue') ? 'font-semibold text-brand-600' : '' }}">{{ __('messages.queue') }}</a>
    </div>

    <h1 class="text-xl font-extrabold text-gray-900 mb-1">{{ __('common.send') }} {{ __('common.message') }}</h1>
    <p class="text-sm text-gray-500 mb-5">{{ __('common.send') }} WhatsApp langsung ke nomor atau {{ __('common.contact') }}</p>

    <form method="POST" action="{{ route('messages.send') }}" class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
        @csrf
        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('common.session') }} WhatsApp</label>
            <select name="session_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <option value="">{{ __('common.select') }} {{ __('common.session') }} {{ __('common.active') }}</option>
                @foreach($sessions as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->phone ?? 'offline' }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('messages.target') }}</label>
            <div class="flex gap-2 mb-2">
                <button type="button" onclick="switchTab('manual')" id="tabManual" class="text-xs px-3 py-1.5 rounded-lg bg-brand-600 text-white font-medium">{{ __('messages.manual_number') }}</button>
                <button type="button" onclick="switchTab('contact')" id="tabContact" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 font-medium">{{ __('messages.from_contact') }}</button>
            </div>
            <div id="panelManual">
                <input type="text" name="phone" placeholder="6281234567890" value="{{ old('phone') }}"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div id="panelContact" class="hidden">
                <select name="contact_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">{{ __('common.select') }} {{ __('common.contact') }}</option>
                    @foreach($contacts as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} — {{ preg_replace('/@.*$/', '', $c->phone) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('common.message') }} <span class="text-gray-400">({{ __('messages.spintax_hint') }})</span></label>
            <textarea name="message" rows="4" required placeholder="{{ __('messages.type_message') }}"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">{{ old('message') }}</textarea>
            <div class="flex items-center gap-2 mt-1 flex-wrap">
                @foreach($templates as $tpl)
                <button type="button" onclick="useTemplate('{{ addslashes($tpl->message) }}')" class="text-[10px] bg-gray-100 text-gray-600 px-2 py-1 rounded-lg hover:bg-gray-200">{{ $tpl->name }}</button>
                @endforeach
            </div>
        </div>

        <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-3 font-semibold text-sm hover:bg-brand-700 transition">
            <i class="fas fa-paper-plane mr-1"></i> {{ __('common.send') }} {{ __('common.message') }}
        </button>
    </form>
</div>

<script>
function switchTab(tab) {
    document.getElementById('panelManual').classList.toggle('hidden', tab !== 'manual');
    document.getElementById('panelContact').classList.toggle('hidden', tab !== 'contact');
    document.getElementById('tabManual').classList.toggle('bg-brand-600 text-white', tab === 'manual');
    document.getElementById('tabManual').classList.toggle('bg-gray-100 text-gray-700', tab !== 'manual');
    document.getElementById('tabContact').classList.toggle('bg-brand-600 text-white', tab === 'contact');
    document.getElementById('tabContact').classList.toggle('bg-gray-100 text-gray-700', tab !== 'contact');
}
function useTemplate(msg) {
    document.querySelector('textarea[name="message"]').value = msg;
}
</script>
@endsection
