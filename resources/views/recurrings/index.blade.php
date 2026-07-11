@extends('layouts.app')
@section('title', __('recurrings.index_title'))
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('recurrings.heading') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('recurrings.subtitle') }}</p>
    </div>
    <button onclick="openCreateModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('common.create') }}
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @php $targetLabels = ['all' => __('common.all'), 'group' => 'Group', 'numbers' => __('recurrings.specific_numbers')]; @endphp
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-4 py-3">{{ __('common.name') }}</th>
                <th class="px-4 py-3">{{ __('recurrings.recurrence') }}</th>
                <th class="px-4 py-3">{{ __('common.time') }}</th>
                <th class="px-4 py-3">{{ __('recurrings.channel') }}</th>
                <th class="px-4 py-3">{{ __('recurrings.target') }}</th>
                <th class="px-4 py-3">{{ __('common.status') }}</th>
                <th class="px-4 py-3">{{ __('recurrings.last_sent') }}</th>
                <th class="px-4 py-3 w-20"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($schedules as $s)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-4 py-3 font-medium text-gray-900">{{ $s->name }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $s->recurrence === 'once' ? 'bg-gray-100 text-gray-700' : '' }}
                        {{ $s->recurrence === 'daily' ? 'bg-blue-50 text-blue-700' : '' }}
                        {{ $s->recurrence === 'weekly' ? 'bg-violet-50 text-violet-700' : '' }}
                        {{ $s->recurrence === 'monthly' ? 'bg-amber-50 text-amber-700' : '' }}">
                        {{ ['once' => __('recurrings.once'), 'daily' => __('recurrings.daily'), 'weekly' => __('recurrings.weekly'), 'monthly' => __('recurrings.monthly')][$s->recurrence] }}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $s->time ?? '-' }}</td>
                <td class="px-4 py-3">
                    @php
                        $ch = $s->channel ?? 'whatsapp';
                        $chBadge = ['whatsapp' => 'bg-emerald-50 text-emerald-700', 'meta' => 'bg-blue-50 text-blue-700', 'telegram' => 'bg-sky-50 text-sky-700'][$ch] ?? 'bg-gray-50 text-gray-700';
                        $chName = ['whatsapp' => 'WA', 'meta' => 'Meta', 'telegram' => 'TG'][$ch] ?? 'WA';
                    @endphp
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium {{ $chBadge }}">{{ $chName }}</span>
                    <span class="text-xs text-gray-500 ml-1">{{ $s->session?->name ?? $s->metaAccount?->name ?? $s->telegramAccount?->name ?? '-' }}</span>
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $targetLabels[$s->target_type] }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('recurrings.toggle', $s) }}">
                        @csrf
                        <button class="text-xs font-medium px-2 py-0.5 rounded-full {{ $s->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $s->is_active ? __('common.active') : __('common.inactive') }}
                        </button>
                    </form>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ $s->last_sent_at?->diffForHumans() ?? '-' }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1">
                        <button onclick="editSchedule({{ $s->id }}, '{{ addslashes($s->name) }}', '{{ $s->recurrence }}', '{{ $s->time }}', '{{ $s->session_id ?? '' }}', '{{ $s->target_type }}', '{{ addslashes($s->message) }}', '{{ $s->channel ?? 'whatsapp' }}', '{{ $s->meta_account_id ?? '' }}', '{{ $s->telegram_account_id ?? '' }}')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                        <form method="POST" action="{{ route('recurrings.destroy', $s) }}" onsubmit="return confirm('{{ __('common.delete') }}?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-16 text-center text-gray-500">{{ __('recurrings.empty') }} <button onclick="openCreateModal()" class="text-brand-600 hover:underline">{{ __('recurrings.empty_cta') }}</button></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Create/Edit Modal --}}
<div id="scheduleModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="modalTitle">{{ __('recurrings.create_modal_title') }}</h2>
        <form method="POST" action="{{ route('recurrings.store') }}" class="space-y-3" id="scheduleForm" x-data="{ schChannel: 'whatsapp' }">
            @csrf
            <div id="methodField"></div>
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="text-xs font-medium text-gray-500">{{ __('common.name') }}</label>
                    <input type="text" name="name" id="fName" placeholder="Welcome Message" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Channel</label>
                    <select name="channel" x-model="schChannel" id="fChannel" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="whatsapp">WhatsApp (Baileys)</option>
                        <option value="meta">WhatsApp Cloud (Meta)</option>
                        <option value="telegram">Telegram</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('recurrings.recurrence') }}</label>
                    <select name="recurrence" id="fRecurrence" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="once">{{ __('recurrings.once') }}</option>
                        <option value="daily">{{ __('recurrings.daily') }}</option>
                        <option value="weekly">{{ __('recurrings.weekly') }}</option>
                        <option value="monthly">{{ __('recurrings.monthly') }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('recurrings.time_label') }}</label>
                    <input type="time" name="time" id="fTime" value="08:00" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div x-show="schChannel === 'whatsapp'">
                    <label class="text-xs font-medium text-gray-500">{{ __('common.session') }}</label>
                    <select name="session_id" id="fSessionId" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="">{{ __('recurrings.auto') }}</option>
                        @foreach($sessions as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="schChannel === 'meta'">
                    <label class="text-xs font-medium text-gray-500">Meta Account</label>
                    <select name="meta_account_id" id="fMetaAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Meta Account</option>
                        @foreach($metaAccounts as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="schChannel === 'telegram'">
                    <label class="text-xs font-medium text-gray-500">Telegram Account</label>
                    <select name="telegram_account_id" id="fTelegramAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Telegram Account</option>
                        @foreach($telegramAccounts as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('recurrings.target') }}</label>
                    <select name="target_type" id="fTargetType" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="all">{{ __('common.all') }} {{ __('common.contact') }}</option>
                        <option value="numbers">{{ __('recurrings.specific_numbers') }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.message') }} <span class="text-gray-400">{{ __('recurrings.spintax_hint') }}, {'{name}'} = {{ __('common.name') }})</span></label>
                <textarea name="message" id="fMessage" rows="3" required placeholder="Halo {name}! {Selamat datang|Hai, apa kabar?}" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('scheduleModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = '{{ __('recurrings.create_modal_title') }}';
    const f = document.getElementById('scheduleForm');
    f.action = '{{ route('recurrings.store') }}';
    f.reset();
    document.getElementById('fTime').value = '08:00';
    document.getElementById('fChannel').value = 'whatsapp';
    document.getElementById('fChannel').dispatchEvent(new Event('input', { bubbles: true }));
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('scheduleModal').classList.remove('hidden');
}

function editSchedule(id, name, recurrence, time, sessionId, targetType, message, channel, metaAccountId, telegramAccountId) {
    document.getElementById('modalTitle').textContent = '{{ __('recurrings.edit_modal_title') }}';
    const f = document.getElementById('scheduleForm');
    f.action = '/recurrings/' + id;
    document.getElementById('fName').value = name;
    document.getElementById('fRecurrence').value = recurrence;
    document.getElementById('fTime').value = time || '08:00';
    document.getElementById('fChannel').value = channel || 'whatsapp';
    document.getElementById('fChannel').dispatchEvent(new Event('input', { bubbles: true }));
    document.getElementById('fSessionId').value = sessionId || '';
    document.getElementById('fMetaAccountId').value = metaAccountId || '';
    document.getElementById('fTelegramAccountId').value = telegramAccountId || '';
    document.getElementById('fTargetType').value = targetType;
    document.getElementById('fMessage').value = message;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('scheduleModal').classList.remove('hidden');
}
</script>
@endsection
