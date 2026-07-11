@extends('layouts.app')

@section('title', __('publishing.calendar_title') . ' — ' . config('app.name'))

@section('content')
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900"><i class="fas fa-calendar-days text-brand-500 mr-2"></i>{{ __('publishing.calendar') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('publishing.calendar_subtitle') }}</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('publishing.index') }}" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition flex items-center gap-2">
            <i class="fas fa-plus"></i> {{ __('publishing.new_post') }}
        </a>
    </div>
</div>

@php
    $firstDay = \Carbon\Carbon::create($year, $month, 1);
    $daysInMonth = $firstDay->daysInMonth;
    $startDow = $firstDay->dayOfWeek;
    $prevMonth = \Carbon\Carbon::create($year, $month, 1)->subMonth();
    $nextMonth = \Carbon\Carbon::create($year, $month, 1)->addMonth();
    $now = \Carbon\Carbon::now();
    $monthName = $firstDay->format('F');
@endphp

{{-- Navigation --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('publishing.calendar', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
        <i class="fas fa-chevron-left"></i> {{ $prevMonth->format('M Y') }}
    </a>
    <h2 class="text-xl font-extrabold text-gray-900">{{ $monthName }} {{ $year }}</h2>
    <a href="{{ route('publishing.calendar', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
        {{ $nextMonth->format('M Y') }} <i class="fas fa-chevron-right"></i>
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 text-center">
        <div class="text-2xl font-extrabold text-brand-600">{{ $scheduledCount }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ __('publishing.upcoming') }}</div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 text-center">
        <div class="text-2xl font-extrabold text-green-600">{{ $publishedCount }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ __('publishing.published') }}</div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 text-center">
        <div class="text-2xl font-extrabold text-gray-600">{{ $firstDay->daysInMonth }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ __('publishing.days_this_month') }}</div>
    </div>
</div>

{{-- Calendar Grid --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="grid grid-cols-7 border-b border-gray-200">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
        <div class="px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $day }}</div>
        @endforeach
    </div>
    <div class="grid grid-cols-7">
        @for($d = 0; $d < $startDow; $d++)
        <div class="min-h-[90px] border-b border-r border-gray-100 bg-gray-50/50 p-1"></div>
        @endfor
        @for($day = 1; $day <= $daysInMonth; $day++)
        @php
            $date = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
            $dayPosts = $posts[$date] ?? collect();
            $isToday = $now->format('Y-m-d') === $date;
        @endphp
        <div class="min-h-[90px] border-b border-r border-gray-100 p-1 {{ $isToday ? 'bg-brand-50' : '' }}">
            <div class="text-xs font-semibold mb-1 {{ $isToday ? 'text-brand-700' : 'text-gray-600' }} px-1">
                {{ $day }}
            </div>
            @foreach($dayPosts->take(3) as $p)
            <div class="text-[10px] px-1 py-0.5 mb-0.5 rounded truncate {{ $p->status === 'published' ? 'bg-green-100 text-green-800' : ($p->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}" title="{{ $p->content }}">
                <span class="inline-block w-1.5 h-1.5 rounded-full {{ $p->status === 'published' ? 'bg-green-500' : ($p->status === 'failed' ? 'bg-red-500' : 'bg-blue-500') }} mr-1"></span>
                {{ \Illuminate\Support\Str::limit($p->content, 20, '...') ?: __('publishing.no_content') }}
            </div>
            @endforeach
            @if($dayPosts->count() > 3)
            <div class="text-[10px] text-gray-400 px-1">+{{ $dayPosts->count() - 3 }} more</div>
            @endif
        </div>
        @endfor
    </div>
</div>

<div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> {{ __('publishing.scheduled') }}</span>
    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> {{ __('publishing.published') }}</span>
    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> {{ __('publishing.failed') }}</span>
</div>
@stop
