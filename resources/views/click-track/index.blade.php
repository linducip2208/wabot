@extends('layouts.app')
@section('title', __('clicktrack.index_title'))
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('clicktrack.heading') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('clicktrack.subtitle') }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="text-3xl font-extrabold text-brand-600">{{ $stats['total_clicks'] ?? 0 }}</div>
        <div class="text-sm text-gray-500 mt-1">{{ __('clicktrack.total_clicks') }}</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="text-3xl font-extrabold text-emerald-600">{{ $stats['unique_contacts'] ?? 0 }}</div>
        <div class="text-sm text-gray-500 mt-1">{{ __('clicktrack.unique_contacts') }}</div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">{{ __('common.contact') }}</th>
                <th class="px-5 py-3 hidden md:table-cell">{{ __('clicktrack.link') }}</th>
                <th class="px-5 py-3">{{ __('clicktrack.click_time') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($events as $e)
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 font-medium">{{ $e->contact->name ?? 'N/A' }}</td>
                <td class="px-5 py-3 text-gray-500 hidden md:table-cell truncate max-w-[300px]">{{ $e->link_url }}</td>
                <td class="px-5 py-3 text-gray-500">{{ $e->clicked_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-5 py-16 text-center text-gray-500">{{ __('clicktrack.empty') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 bg-gray-50 rounded-xl p-4 text-xs text-gray-500">
    <i class="fas fa-info-circle text-brand-500 mr-1"></i> {{ __('clicktrack.footer_hint') }}
</div>

@endsection
