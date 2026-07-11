@extends('layouts.app')

@section('title', __('knowledge.title'))

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('knowledge.title') }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ __('knowledge.subtitle') }}</p>

    @if(session('success'))
        <div class="bg-green-50 border border-green-300 text-green-800 rounded-xl px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-300 text-red-800 rounded-xl px-4 py-3 mb-4 text-sm">{{ session('error') }}</div>
    @endif

    {{-- FORM: FAQ Manual --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 shadow-sm">
        <h2 class="font-semibold text-gray-800 mb-3">{{ __('knowledge.add_faq') }}</h2>
        <form method="POST" action="{{ route('knowledge.store') }}" id="faqForm">
            @csrf
            <input type="hidden" name="type" value="faq">
            <div class="mb-3">
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }} Knowledge</label>
                <input name="title" required placeholder="{{ __('knowledge.name_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div id="faqRows">
                <div class="faq-row border border-gray-200 rounded-xl p-3 mb-2 bg-gray-50/50">
                    <div class="grid grid-cols-12 gap-2">
                        <input name="faqs[0][category]" placeholder="{{ __('common.category') }} ({{ __('common.optional') }})" class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                        <input name="faqs[0][question]" placeholder="{{ __('knowledge.question') }}" required class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                        <input name="faqs[0][answer]" placeholder="{{ __('knowledge.answer') }}" required class="col-span-3 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                        <button type="button" class="col-span-1 text-red-500 text-xs delete-row hidden">✕</button>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 mt-2">
                <button type="button" onclick="addRow()" class="text-xs bg-gray-100 border border-gray-300 rounded-lg px-3 py-1.5 hover:bg-gray-200">+ {{ __('knowledge.add_row') }}</button>
                <button type="submit" class="text-xs bg-brand-600 text-white rounded-lg px-4 py-1.5 hover:bg-brand-700">{{ __('knowledge.save_faq') }}</button>
            </div>
        </form>
    </div>

    {{-- FORM: CSV Upload --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 shadow-sm">
        <h2 class="font-semibold text-gray-800 mb-3">{{ __('knowledge.upload_csv') }}</h2>
        <form method="POST" action="{{ route('knowledge.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }} Knowledge</label>
                <input name="title" required placeholder="{{ __('knowledge.csv_name_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="flex gap-2 items-center">
                <input type="file" name="file" accept=".csv,.txt" required class="text-xs border border-gray-300 rounded-lg px-3 py-1.5 file:mr-3 file:py-1 file:px-3 file:border-0 file:bg-brand-50 file:text-brand-700 file:rounded-lg file:text-xs">
                <button type="submit" class="text-xs bg-brand-600 text-white rounded-lg px-4 py-1.5 hover:bg-brand-700">{{ __('knowledge.upload_csv_btn') }}</button>
            </div>
            <p class="text-xs text-gray-400 mt-2">{{ __('knowledge.csv_format') }} <code>question,answer</code> ({{ __('knowledge.csv_format_or') }} <code>category,question,answer</code>). <a href="#" onclick="downloadSample()" class="text-brand-600 underline">{{ __('knowledge.download_sample') }}</a></p>
        </form>
    </div>

    {{-- DAFTAR Knowledge --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
        <h2 class="font-semibold text-gray-800 mb-3">{{ __('knowledge.list') }} ({{ $entries->count() }})</h2>
        @forelse($entries as $e)
            @php $rows = $e->rows; @endphp
            <div class="border border-gray-100 rounded-xl p-4 mb-3 bg-gray-50/30">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <span class="font-semibold text-sm">{{ $e->title }}</span>
                        <span class="text-xs text-gray-400 ml-2">{{ strtoupper($e->type) }} · {{ count($rows) }} {{ __('common.items') }}</span>
                        @if($e->is_active)
                            <span class="ml-2 text-xs text-green-700 bg-green-50 px-2 py-0.5 rounded-full">{{ __('common.active') }}</span>
                        @else
                            <span class="ml-2 text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ __('common.inactive') }}</span>
                        @endif
                    </div>
                    <div class="flex gap-1">
                        <form method="POST" action="{{ route('knowledge.toggle', $e) }}" class="inline">
                            @csrf
                            <button class="text-xs {{ $e->is_active ? 'text-amber-600' : 'text-green-600' }} bg-white border border-gray-200 rounded-lg px-2 py-1">{{ $e->is_active ? __('knowledge.deactivate') : __('knowledge.activate') }}</button>
                        </form>
                        <form method="POST" action="{{ route('knowledge.destroy', $e) }}" class="inline" onsubmit="return confirm('{{ __('knowledge.delete_confirm') }}')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 bg-white border border-gray-200 rounded-lg px-2 py-1">{{ __('common.delete') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">{{ __('knowledge.empty') }}</p>
        @endforelse
    </div>
</div>

<script>
let faqIndex = 1;
function addRow() {
    const wrap = document.getElementById('faqRows');
    const row = document.createElement('div');
    row.className = 'faq-row border border-gray-200 rounded-xl p-3 mb-2 bg-gray-50/50';
    row.innerHTML = `<div class="grid grid-cols-12 gap-2">
        <input name="faqs[${faqIndex}][category]" placeholder="{{ __('common.category') }} ({{ __('common.optional') }})" class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
        <input name="faqs[${faqIndex}][question]" placeholder="{{ __('knowledge.question') }}" required class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
        <input name="faqs[${faqIndex}][answer]" placeholder="{{ __('knowledge.answer') }}" required class="col-span-3 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
        <button type="button" class="col-span-1 text-red-500 text-xs" onclick="this.parentElement.parentElement.remove()">✕</button>
    </div>`;
    wrap.appendChild(row);
    faqIndex++;
}
function downloadSample() {
    const csv = 'category,question,answer\n{{ __('knowledge.sample_category') }},{{ __('knowledge.sample_q1') }},{{ __('knowledge.sample_a1') }}\n{{ __('knowledge.sample_category2') }},{{ __('knowledge.sample_q2') }},{{ __('knowledge.sample_a2') }}\n{{ __('knowledge.sample_category3') }},{{ __('knowledge.sample_q3') }},{{ __('knowledge.sample_a3') }}.';
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = 'knowledge-sample.csv'; a.click();
}
</script>
@endsection
