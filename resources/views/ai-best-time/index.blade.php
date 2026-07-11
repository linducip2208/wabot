@extends('layouts.app')
@section('title', __('aibesttime.title'))
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('aibesttime.title') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('aibesttime.subtitle') }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    {{-- Input Panel --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-20">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-clock text-brand-500"></i> {{ __('aibesttime.analyze') }}
            </h2>
            <form method="POST" action="{{ route('ai-best-time.suggest') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('aibesttime.platform') }} <span class="text-red-400">*</span></label>
                    <select name="platform" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        @foreach($platforms as $p)
                            <option value="{{ $p }}" {{ old('platform', session('selected_platform')) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('aibesttime.niche') }}</label>
                    <input type="text" name="niche" placeholder="{{ __('aibesttime.niche_placeholder') }}" value="{{ old('niche') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('aibesttime.target_audience') }}</label>
                    <input type="text" name="target_audience" placeholder="{{ __('aibesttime.audience_placeholder') }}" value="{{ old('target_audience') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('aibesttime.timezone') }}</label>
                    <select name="timezone" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="WIB (UTC+7)">WIB (UTC+7) — Jakarta</option>
                        <option value="WITA (UTC+8)">WITA (UTC+8) — Bali, Makassar</option>
                        <option value="WIT (UTC+9)">WIT (UTC+9) — Papua</option>
                        <option value="UTC">UTC</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-orange-500 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:from-amber-600 hover:to-orange-600 transition flex items-center justify-center gap-2 card-lift">
                    <i class="fas fa-magnifying-glass-chart"></i> {{ __('aibesttime.get_recommendations') }}
                </button>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="lg:col-span-2 space-y-4">
        @php $recs = session('recommendations'); @endphp
        @if($recs)
        <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-4 text-white">
                <div class="flex items-center gap-2">
                    <i class="fas fa-clock text-lg"></i>
                    <div>
                        <h3 class="font-bold">{{ __('aibesttime.recommendations_for') }} {{ ucfirst($recs['platform'] ?? session('selected_platform', '')) }}</h3>
                        <p class="text-sm text-amber-100">{{ $recs['timezone'] ?? 'WIB (UTC+7)' }}</p>
                    </div>
                </div>
            </div>

            <div class="p-5">
                @php $schedule = $recs['schedule'] ?? []; @endphp
                @if(count($schedule) > 0)
                    {{-- Heatmap-style Grid --}}
                    <div class="overflow-x-auto mb-5">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr>
                                    <th class="text-left py-2 px-2 text-[11px] font-semibold text-gray-400 uppercase w-20">{{ __('aibesttime.time') }}</th>
                                    @foreach(array_keys($schedule) as $day)
                                        <th class="text-center py-2 px-2 text-[11px] font-semibold text-gray-600 w-16">{{ Str::limit($day, 3, '') }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $timeSlots = ['06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00'];
                                    $dayKeys = array_keys($schedule);
                                @endphp
                                @foreach($timeSlots as $slot)
                                <tr class="border-t border-gray-100">
                                    <td class="py-1.5 px-2 text-[10px] text-gray-400">{{ $slot }}</td>
                                    @foreach($dayKeys as $day)
                                        @php
                                            $slots = $schedule[$day] ?? [];
                                            $match = collect($slots)->first(fn($s) => ($s['time'] ?? '') === $slot);
                                            $score = $match['score'] ?? 0;
                                            $bg = $score >= 85 ? 'bg-emerald-100 text-emerald-800' : ($score >= 70 ? 'bg-lime-100 text-lime-800' : ($score >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-transparent'));
                                        @endphp
                                        <td class="text-center py-1.5 px-1">
                                            @if($score > 0)
                                                <span class="inline-block w-full text-[10px] font-semibold rounded px-1 py-0.5 {{ $bg }}" title="{{ $match['reason'] ?? '' }} — Score: {{ $score }}">{{ $score }}</span>
                                            @else
                                                <span class="text-[10px] text-gray-200">&middot;</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Score Legend --}}
                    <div class="flex items-center gap-3 mb-5 text-[10px] text-gray-500">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-100"></span> 85-100 ({{ __('aibesttime.best') }})</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-lime-100"></span> 70-84 ({{ __('aibesttime.good') }})</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-50"></span> 50-69 ({{ __('aibesttime.okay') }})</span>
                    </div>

                    {{-- Top Recommendations --}}
                    @php
                        $allSlots = [];
                        foreach ($schedule as $day => $slots) {
                            foreach ($slots as $s) {
                                $allSlots[] = array_merge($s, ['day' => $day]);
                            }
                        }
                        usort($allSlots, fn($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));
                        $topSlots = array_slice($allSlots, 0, 10);
                    @endphp
                    <div class="mb-5">
                        <h4 class="font-semibold text-gray-900 text-sm mb-2">{{ __('aibesttime.top_recommendations') }}</h4>
                        <div class="grid sm:grid-cols-2 gap-2">
                            @foreach($topSlots as $slot)
                            <div class="flex items-center gap-2 p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold {{ ($slot['score'] ?? 0) >= 85 ? 'bg-emerald-500 text-white' : (($slot['score'] ?? 0) >= 70 ? 'bg-lime-500 text-white' : 'bg-amber-400 text-white') }}">{{ $slot['score'] ?? '?' }}</div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-800">{{ $slot['day'] }} {{ $slot['time'] ?? '' }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $slot['reason'] ?? '' }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Tips --}}
                    @php $tips = $recs['tips'] ?? []; @endphp
                    @if(count($tips) > 0)
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm mb-2">{{ __('aibesttime.tips') }}</h4>
                        <div class="space-y-1.5">
                            @foreach($tips as $tip)
                                <div class="flex items-start gap-2 text-xs text-gray-600">
                                    <i class="fas fa-lightbulb text-amber-400 mt-0.5 flex-shrink-0"></i>
                                    <span>{{ $tip }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @elseif(isset($recs['raw']))
                    <div class="text-sm text-gray-600 whitespace-pre-wrap">{{ $recs['raw'] }}</div>
                @endif
            </div>
        </div>
        @else
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-20 h-20 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-clock text-3xl text-amber-400"></i>
            </div>
            <h3 class="text-lg font-extrabold text-gray-900 mb-2">{{ __('aibesttime.empty_title') }}</h3>
            <p class="text-gray-500 text-sm max-w-md mx-auto">{{ __('aibesttime.empty_desc') }}</p>
        </div>
        @endif
    </div>
</div>

@endsection
