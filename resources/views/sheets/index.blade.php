@extends('layouts.app')
@section('title', __('sheets.title') . ' — WABot')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">{{ __('sheets.title') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('sheets.subtitle') }}</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
            class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> {{ __('sheets.create_integration') }}
        </button>
    </div>

    @if($integrations->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-emerald-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-table text-emerald-500 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1">{{ __('sheets.empty_title') }}</h3>
            <p class="text-sm text-gray-400 mb-4">{{ __('sheets.empty_desc') }}</p>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
                {{ __('sheets.create_integration') }}
            </button>
        </div>
    @else
        <div class="space-y-4">
            @foreach($integrations as $integration)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-file-excel text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h3 class="font-semibold text-gray-900">{{ $integration->name }}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $integration->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $integration->is_active ? __('common.active') : __('common.inactive') }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                    {{ $directions[$integration->sync_direction] ?? $integration->sync_direction }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-400 flex-wrap">
                                <span><i class="fas fa-table mr-1"></i>{{ $integration->spreadsheet_id }}</span>
                                <span><i class="fas fa-tag mr-1"></i>{{ $integration->sheet_name }}</span>
                                <span>
                                    <i class="fas fa-sync mr-1"></i>
                                    @if($integration->sync_status === 'synced')
                                        {{ __('store.synced') }} {{ $integration->last_synced_at?->diffForHumans() }}
                                    @elseif($integration->sync_status === 'syncing')
                                        {{ __('store.syncing') }}...
                                    @elseif($integration->sync_status === 'failed')
                                        <span class="text-red-500">{{ __('store.sync_failed') }}</span>
                                    @else
                                        {{ __('store.never_synced') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        @if(!$integration->is_active)
                            <form action="{{ route('sheets.connect', $integration) }}" method="POST">
                                @csrf
                                <button class="px-3 py-2 rounded-xl text-xs font-medium bg-green-600 text-white hover:bg-green-700 transition">
                                    <i class="fas fa-plug mr-1"></i>{{ __('store.test_connect') }}
                                </button>
                            </form>
                        @else
                            <form action="{{ route('sheets.sync', $integration) }}" method="POST">
                                @csrf
                                <button class="px-3 py-2 rounded-xl text-xs font-medium bg-emerald-600 text-white hover:bg-emerald-700 transition" {{ $integration->sync_status === 'syncing' ? 'disabled' : '' }}>
                                    <i class="fas fa-sync mr-1 {{ $integration->sync_status === 'syncing' ? 'fa-spin' : '' }}"></i>{{ __('sheets.sync_now') }}
                                </button>
                            </form>
                        @endif
                        <button onclick="openEditModal({{ $integration->id }})"
                            class="px-2.5 py-2 rounded-xl text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form method="POST" action="{{ route('sheets.destroy', $integration) }}" onsubmit="return confirm('{{ __('common.delete') }}?')">
                            @csrf @method('DELETE')
                            <button class="px-2.5 py-2 rounded-xl text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 transition">
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

{{-- Setup Guide --}}
<div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6 shadow-sm">
    <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fas fa-book text-brand-500"></i> Setup Guide
    </h2>
    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-2">1. Google Cloud Console</h3>
            <ol class="text-xs text-gray-600 space-y-1.5 list-decimal ml-4">
                <li>Buka <a href="https://console.cloud.google.com" target="_blank" class="text-brand-600 underline">console.cloud.google.com</a></li>
                <li>Buat project baru atau pilih existing</li>
                <li><strong>APIs &amp; Services → Library</strong> → cari <code class="bg-gray-100 px-1 rounded">Google Sheets API</code> → Enable</li>
                <li><strong>APIs &amp; Services → Credentials</strong> → Create Credentials → <strong>Service Account</strong></li>
                <li>Isi nama (misal: wabot-sheets), klik <strong>Create &amp; Continue</strong></li>
                <li>Role: <strong>Editor</strong> (atau skip), klik <strong>Done</strong></li>
            </ol>
        </div>
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-2">2. Download JSON Key</h3>
            <ol class="text-xs text-gray-600 space-y-1.5 list-decimal ml-4">
                <li>Klik service account yang baru dibuat</li>
                <li>Tab <strong>Keys → Add Key → Create New Key → JSON</strong></li>
                <li>File JSON akan otomatis terdownload</li>
                <li>Buka file JSON, copy <strong>seluruh isinya</strong></li>
                <li>Paste ke field <strong>Service Account JSON</strong> di form WABot</li>
            </ol>

            <h3 class="text-sm font-semibold text-gray-700 mt-4 mb-2">3. Share Google Sheet</h3>
            <ol class="text-xs text-gray-600 space-y-1.5 list-decimal ml-4">
                <li>Buka Google Sheet yang ingin disambungkan</li>
                <li>Klik <strong>Share</strong> (tombol hijau kanan atas)</li>
                <li>Masukkan email service account (ada di file JSON: <code class="bg-gray-100 px-1 rounded">...@...iam.gserviceaccount.com</code>)</li>
                <li>Role: <strong>Editor</strong>, klik <strong>Send</strong></li>
                <li>Copy <strong>Spreadsheet ID</strong> dari URL:<br><code class="bg-gray-100 px-1 rounded text-[10px]">https://docs.google.com/spreadsheets/d/<strong>INI_SPREADSHEET_ID</strong>/edit</code></li>
            </ol>

            <h3 class="text-sm font-semibold text-gray-700 mt-4 mb-2">4. WABot</h3>
            <ol class="text-xs text-gray-600 space-y-1.5 list-decimal ml-4">
                <li>Klik <strong>Tambah Integrasi</strong> di atas</li>
                <li>Isi nama, Spreadsheet ID, nama sheet (misal: Sheet1)</li>
                <li>Pilih arah sync: Import / Export / Both</li>
                <li>Paste Service Account JSON</li>
                <li>Klik <strong>Simpan</strong> → klik <strong>Test Connect</strong></li>
            </ol>
        </div>
    </div>
    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800">
        <i class="fas fa-lightbulb mr-1"></i> <strong>Format CSV/Sheet:</strong> Kolom <code>name</code>, <code>phone</code> wajib. Kolom <code>email</code>, <code>tags</code>, <code>notes</code> opsional. Baris pertama = header.
    </div>
</div>

{{-- Add Modal --}}
<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">{{ __('sheets.create_integration') }}</h2>
        <form method="POST" action="{{ route('sheets.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }}</label>
                <input type="text" name="name" required placeholder="{{ __('sheets.name_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('sheets.spreadsheet_id') }}</label>
                    <input type="text" name="spreadsheet_id" required placeholder="1BxiMVs0..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('sheets.sheet_name') }}</label>
                    <input type="text" name="sheet_name" value="Sheet1" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('sheets.sync_direction') }}</label>
                <select name="sync_direction" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="import">{{ __('sheets.import') }}</option>
                    <option value="export">{{ __('sheets.export') }}</option>
                    <option value="both">{{ __('sheets.both') }}</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('sheets.service_account_json') }}</label>
                <textarea name="service_account_json" rows="5" required placeholder='{"type": "service_account", "project_id": "..."}' class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                <p class="text-[11px] text-gray-400 mt-1">{{ __('sheets.json_hint') }}</p>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">{{ __('common.edit') }} {{ __('sheets.integration') }}</h2>
        <form method="POST" id="editForm" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }}</label>
                <input type="text" name="name" id="editName" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('sheets.spreadsheet_id') }}</label>
                    <input type="text" name="spreadsheet_id" id="editSpreadsheetId" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('sheets.sheet_name') }}</label>
                    <input type="text" name="sheet_name" id="editSheetName" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('sheets.sync_direction') }}</label>
                <select name="sync_direction" id="editDirection" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="import">{{ __('sheets.import') }}</option>
                    <option value="export">{{ __('sheets.export') }}</option>
                    <option value="both">{{ __('sheets.both') }}</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('sheets.service_account_json') }} <span class="text-gray-400">({{ __('common.leave_empty_to_keep') }})</span></label>
                <textarea name="service_account_json" rows="4" placeholder='{"type": "service_account", ...}' class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditModal(id) {
    const integrations = @json($integrations->keyBy('id'));
    const data = integrations[id];
    if (!data) return;

    document.getElementById('editName').value = data.name;
    document.getElementById('editSpreadsheetId').value = data.spreadsheet_id;
    document.getElementById('editSheetName').value = data.sheet_name;
    document.getElementById('editDirection').value = data.sync_direction;
    document.getElementById('editForm').action = '/sheets/' + id;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
