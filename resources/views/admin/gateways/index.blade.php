@extends('layouts.app')
@section('title', 'Payment Gateway — Admin')
@section('content')

@php
$drivers = ['manual' => 'Manual Transfer', 'stripe' => 'Stripe', 'razorpay' => 'Razorpay'];
$driverIcons = ['manual' => 'fa-money-check', 'stripe' => 'fa-cc-stripe', 'razorpay' => 'fa-rupee-sign'];
$driverColors = ['manual' => '#6b7280', 'stripe' => '#635bff', 'razorpay' => '#02042b'];
@endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Payment Gateway</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $gateways->count() }} gateway tersedia</p>
    </div>
    <button onclick="openCreate()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('common.create') }} Gateway
    </button>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
    @foreach($gateways as $g)
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift {{ $g->is_active ? '' : 'opacity-50' }}">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs" style="background: {{ $g->logo_color ?? ($driverColors[$g->driver] ?? '#3b82f6') }}">
                    <i class="fas {{ $driverIcons[$g->driver] ?? 'fa-money-check' }}"></i>
                </div>
                <div>
                    <span class="font-semibold text-sm text-gray-900">{{ $g->name }}</span>
                    @if($g->driver)
                        <span class="block text-[10px] text-gray-400 uppercase">{{ $drivers[$g->driver] ?? $g->driver }}</span>
                    @endif
                </div>
            </div>
            <span class="text-[10px] px-1.5 py-0.5 rounded font-medium {{ $g->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $g->is_active ? __('common.active') : __('common.inactive') }}
            </span>
        </div>
        <div class="text-xs text-gray-500 space-y-0.5 mb-3">
            @if($g->account_number)<div><span class="text-gray-400">Rek:</span> {{ $g->account_number }}</div>@endif
            @if($g->account_holder)<div><span class="text-gray-400">{{ __('common.name') }}:</span> {{ $g->account_holder }}</div>@endif
            @if($g->is_auto)<div class="text-emerald-600"><i class="fas fa-bolt mr-1"></i>Auto-capture</div>@endif
        </div>
        <div class="text-[11px] text-gray-400 bg-gray-50 rounded-lg p-2 max-h-20 overflow-y-auto whitespace-pre-line mb-3">{{ \Str::limit($g->instructions, 80) }}</div>
        <div class="flex gap-1">
            <button onclick="editGateway({{ $g->id }})"
                class="flex-1 text-[11px] bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg py-1.5 font-medium text-center">{{ __('common.edit') }}</button>
            <form method="POST" action="{{ route('admin.gateways.destroy', $g) }}" class="flex-1" onsubmit="return confirm('{{ __('common.delete') }}?')">
                @csrf @method('DELETE')
                <button class="w-full text-[11px] bg-red-50 text-red-600 hover:bg-red-100 rounded-lg py-1.5 font-medium">{{ __('common.delete') }}</button>
            </form>
        </div>
    </div>
    @endforeach
</div>

{{-- Modal --}}
<div id="gwModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="gwModalTitle">{{ __('common.create') }} Gateway</h2>
        <form method="POST" action="{{ route('admin.gateways.store') }}" class="space-y-3" id="gwForm">
            @csrf
            <div id="gwMethodField"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.name') }}</label>
                    <input type="text" name="name" id="gwName" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Kode</label>
                    <input type="text" name="code" id="gwCode" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Driver</label>
                <select name="driver" id="gwDriver" onchange="toggleDriverFields()" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="manual">Manual Transfer</option>
                    <option value="stripe">Stripe</option>
                    <option value="razorpay">Razorpay</option>
                </select>
            </div>

            {{-- Manual fields --}}
            <div id="manualFields">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500">No. Rekening / Tujuan</label>
                        <input type="text" name="account_number" id="gwAccNum" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">{{ __('common.name') }} Pemilik</label>
                        <input type="text" name="account_holder" id="gwAccHolder" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="text-xs font-medium text-gray-500">Instruksi {{ __('common.payment') }}</label>
                    <textarea name="instructions" id="gwInstructions" rows="3" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                </div>
            </div>

            {{-- Stripe / Razorpay API fields --}}
            <div id="apiFields" class="hidden">
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500" id="apiKeyLabel">API Key</label>
                        <input type="password" name="api_key" id="gwApiKey" placeholder="sk_live_..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500" id="apiSecretLabel">Webhook Secret</label>
                        <input type="password" name="api_secret" id="gwApiSecret" placeholder="whsec_..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_auto" id="gwIsAuto" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <label for="gwIsAuto" class="text-sm text-gray-600">Auto-capture {{ __('common.payment') }}</label>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.color') }}</label>
                    <input type="color" name="logo_color" id="gwColor" value="#3b82f6" class="w-full h-10 rounded-xl border border-gray-300 px-2 py-1">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Urutan</label>
                    <input type="number" name="sort_order" id="gwSortOrder" value="0" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('gwModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const gatewaysData = @json($gateways->keyBy('id'));

function toggleDriverFields() {
    const driver = document.getElementById('gwDriver').value;
    const manual = document.getElementById('manualFields');
    const api = document.getElementById('apiFields');
    if (driver === 'manual') {
        manual.classList.remove('hidden');
        api.classList.add('hidden');
    } else {
        manual.classList.add('hidden');
        api.classList.remove('hidden');
        if (driver === 'stripe') {
            document.getElementById('apiKeyLabel').textContent = 'Secret Key';
            document.getElementById('apiSecretLabel').textContent = 'Webhook Secret';
            document.getElementById('gwApiKey').placeholder = 'sk_live_...';
            document.getElementById('gwApiSecret').placeholder = 'whsec_...';
        } else {
            document.getElementById('apiKeyLabel').textContent = 'Key ID';
            document.getElementById('apiSecretLabel').textContent = 'Key Secret';
            document.getElementById('gwApiKey').placeholder = 'rzp_live_...';
            document.getElementById('gwApiSecret').placeholder = '...';
        }
    }
}

function openCreate() {
    document.getElementById('gwModalTitle').textContent = '{{ __('common.create') }} Gateway';
    const f = document.getElementById('gwForm');
    f.action = '{{ route('admin.gateways.store') }}';
    f.reset();
    document.getElementById('gwMethodField').innerHTML = '';
    document.getElementById('gwDriver').value = 'manual';
    toggleDriverFields();
    document.getElementById('gwModal').classList.remove('hidden');
}

function editGateway(id) {
    const g = gatewaysData[id];
    if (!g) return;

    document.getElementById('gwModalTitle').textContent = 'Edit Gateway';
    const f = document.getElementById('gwForm');
    f.action = '/admin/gateways/' + id;
    document.getElementById('gwName').value = g.name;
    document.getElementById('gwCode').value = g.code;
    document.getElementById('gwDriver').value = g.driver || 'manual';
    document.getElementById('gwAccNum').value = g.account_number || '';
    document.getElementById('gwAccHolder').value = g.account_holder || '';
    document.getElementById('gwInstructions').value = g.instructions || '';
    document.getElementById('gwColor').value = g.logo_color || '#3b82f6';
    document.getElementById('gwSortOrder').value = g.sort_order || 0;
    document.getElementById('gwIsAuto').checked = g.is_auto || false;
    toggleDriverFields();
    document.getElementById('gwMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('gwModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
