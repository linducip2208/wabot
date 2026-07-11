@extends('layouts.app')
@section('title', 'Submissions: ' . $form->name . ' — WABot')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('forms.index') }}" class="text-gray-400 hover:text-brand-600 transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-extrabold text-gray-900">Submissions: {{ $form->name }}</h1>
            </div>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('common.total') }}: {{ $submissions->total() }}</p>
        </div>
        <a href="{{ route('forms.export', $form) }}"
            class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
    </div>

    @if($submissions->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-envelope-open-text text-gray-300 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1">{{ __('submissions.empty') }}</h3>
            <p class="text-sm text-gray-400">{{ __('submissions.empty_hint') }}</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3">#</th>
                            <th class="px-5 py-3">Phone</th>
                            <th class="px-5 py-3">{{ __('common.contact') }}</th>
                            @foreach($form->components ?? [] as $comp)
                                <th class="px-5 py-3">{{ $comp['label'] ?? '' }}</th>
                            @endforeach
                            <th class="px-5 py-3">{{ __('common.date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($submissions as $i => $sub)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-3 text-gray-500">{{ $submissions->firstItem() + $i }}</td>
                                <td class="px-5 py-3 font-mono text-xs">{{ $sub->phone }}</td>
                                <td class="px-5 py-3">{{ $sub->contact?->name ?? '-' }}</td>
                                @foreach($form->components ?? [] as $comp)
                                    @php $key = $comp['label'] ?? ''; @endphp
                                    <td class="px-5 py-3">{{ $sub->data[$key] ?? '-' }}</td>
                                @endforeach
                                <td class="px-5 py-3 text-gray-400 text-xs">{{ $sub->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $submissions->links() }}</div>
    @endif
</div>
@endsection
