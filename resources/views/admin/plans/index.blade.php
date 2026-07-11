@extends('layouts.app')
@section('title', 'Kelola ' . __('common.plan') . ' — WABot')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">{{ __('admin.plan_mgmt') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('admin.plan_mgmt_desc') }}</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
            class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> {{ __('common.create') }} {{ __('common.plan') }}
        </button>
    </div>

    <div class="space-y-3">
        @foreach($plans as $plan)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-2">
                            <h3 class="font-semibold text-gray-900 text-lg">{{ $plan->name }}</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $plan->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                            <span class="text-xs text-gray-400">/{{ $plan->billing_period }}</span>
                        </div>

                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xl font-extrabold text-gray-900">
                                {{ $plan->price > 0 ? 'Rp ' . number_format($plan->price, 0, ',', '.') : __('common.free') }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-1.5 mb-3">
                            @php $featList = is_string($plan->features) ? json_decode($plan->features, true) : ($plan->features ?? []); @endphp
                            @foreach($featList as $f)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600">{{ $f }}</span>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $plan->can_use_meta ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                <span class="text-gray-500">Meta API ({{ $plan->max_meta_accounts }})</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $plan->can_use_forms ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                <span class="text-gray-500">Forms ({{ $plan->max_forms }})</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $plan->can_use_calling ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                <span class="text-gray-500">Calling</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $plan->can_use_flow ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                <span class="text-gray-500">Flow</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $plan->can_use_deals ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                <span class="text-gray-500">CRM</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $plan->can_use_commerce ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                <span class="text-gray-500">Commerce</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $plan->can_use_ai_agent ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                                <span class="text-gray-500">AI Agents</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                            <span>{{ $plan->max_sessions }} {{ __('common.session') }}</span>
                            <span>{{ number_format($plan->max_contacts) }} {{ __('common.contact') }}</span>
                            <span>{{ $plan->max_autoreplies }} autoreply</span>
                            <span>{{ number_format($plan->max_campaign_recipients) }} kampanye</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button onclick="openEditModal({{ $plan->id }})"
                            class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600 transition">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('{{ __('common.delete') }} {{ __('common.plan') }}?')" class="inline">
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
</div>

{{-- ADD MODAL --}}
<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('common.create') }} {{ __('common.plan') }}</h2>
        <form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.name') }}</label>
                    <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Slug</label>
                    <input type="text" name="slug" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.period') }}</label>
                    <select name="billing_period" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="lifetime">Lifetime</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.price') }}</label>
                    <input type="number" name="price" value="0" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max {{ __('common.session') }}</label>
                    <input type="number" name="max_sessions" value="1" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max {{ __('common.contact') }}</label>
                    <input type="number" name="max_contacts" value="100" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max Kampanye</label>
                    <input type="number" name="max_campaign_recipients" value="50" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max Autoreply</label>
                    <input type="number" name="max_autoreplies" value="10" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max Meta Akun</label>
                    <input type="number" name="max_meta_accounts" value="0" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max Forms</label>
                    <input type="number" name="max_forms" value="0" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-3">
                <span class="text-xs font-medium text-gray-500 mb-2 block">Fitur Boolean</span>
                <div class="grid grid-cols-4 gap-2">
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_manage_server"> Manage {{ __('common.server') }}</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_meta" checked> Meta API</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_forms"> WA Forms</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_calling"> WA Calling</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_instagram"> Instagram</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_flow"> Flow Builder</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_ai_agent"> AI Agents</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_intent"> Intent</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_drip"> Drip Campaign</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_ab_test"> A/B Test</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_catalog"> Catalog</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_commerce"> Commerce</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_deals"> CRM Deals</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_kanban"> Kanban</label>
                </div>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- {{ __('common.edit') }} MODALS --}}
@foreach($plans as $plan)
<div id="editModal{{ $plan->id }}" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('common.edit') }}: {{ $plan->name }}</h2>
        <form action="{{ route('admin.plans.update', $plan) }}" method="POST" class="space-y-3">
            @csrf @method('PUT')
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.name') }}</label>
                    <input type="text" name="name" value="{{ $plan->name }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Slug</label>
                    <input type="text" name="slug" value="{{ $plan->slug }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.period') }}</label>
                    <select name="billing_period" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="monthly" {{ $plan->billing_period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ $plan->billing_period === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        <option value="lifetime" {{ $plan->billing_period === 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-3">
                <div><label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.price') }}</label><input type="number" name="price" value="{{ $plan->price }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max {{ __('common.session') }}</label><input type="number" name="max_sessions" value="{{ $plan->max_sessions }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max {{ __('common.contact') }}</label><input type="number" name="max_contacts" value="{{ $plan->max_contacts }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max Kampanye</label><input type="number" name="max_campaign_recipients" value="{{ $plan->max_campaign_recipients }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max Autoreply</label><input type="number" name="max_autoreplies" value="{{ $plan->max_autoreplies }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max Meta Akun</label><input type="number" name="max_meta_accounts" value="{{ $plan->max_meta_accounts }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max Forms</label><input type="number" name="max_forms" value="{{ $plan->max_forms }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
            </div>

            <div class="bg-gray-50 rounded-xl p-3">
                <span class="text-xs font-medium text-gray-500 mb-2 block">Fitur Boolean</span>
                <div class="grid grid-cols-4 gap-2">
                    @php $bools = ['can_manage_server','can_use_meta','can_use_forms','can_use_calling','can_use_instagram','can_use_flow','can_use_ai_agent','can_use_intent','can_use_drip','can_use_ab_test','can_use_catalog','can_use_commerce','can_use_deals','can_use_kanban']; @endphp
                    @foreach($bools as $b)
                        <label class="flex items-center gap-1.5 text-xs text-gray-600">
                            <input type="checkbox" name="{{ $b }}" {{ $plan->$b ? 'checked' : '' }}>
                            {{ str_replace(['can_use_','can_'], '', str_replace('_',' ',$b)) }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('editModal{{ $plan->id }}').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Update</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script>
function openEditModal(id) { document.getElementById('editModal'+id).classList.remove('hidden'); }
</script>
@endpush
