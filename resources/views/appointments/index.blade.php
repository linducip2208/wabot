@extends('layouts.app')
@section('title', __('appointments.title') . ' — WABot')

@section('content')
<div class="max-w-7xl mx-auto" x-data="appointmentManager()">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">{{ __('appointments.title') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('appointments.subtitle') }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button @click="showBookingModal = true; loadSlots()"
                class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
                <i class="fas fa-plus text-xs"></i> {{ __('appointments.new_booking') }}
            </button>
            <button @click="showServiceModal = true"
                class="bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fas fa-cog text-xs"></i> {{ __('appointments.manage_services') }}
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Calendar --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">{{ $calendar['monthName'] }} {{ $calendar['year'] }}</h2>
                <div class="flex gap-2">
                    <a href="?month={{ $calendar['prevMonth'] }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition">
                        <i class="fas fa-chevron-left text-sm"></i>
                    </a>
                    <a href="?month={{ now()->format('Y-m') }}" class="px-3 py-1.5 rounded-lg hover:bg-gray-100 text-sm font-medium text-gray-600 transition">
                        {{ __('common.this_month') }}
                    </a>
                    <a href="?month={{ $calendar['nextMonth'] }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-1">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $label)
                    <div class="text-center text-[11px] font-semibold text-gray-500 uppercase py-1.5">{{ $label }}</div>
                @endforeach

                @php $day = 1; @endphp
                @for($week = 0; $week < 6; $week++)
                    @for($dow = 0; $dow < 7; $dow++)
                        @if(($week === 0 && $dow < $calendar['firstDayOfWeek']) || $day > $calendar['daysInMonth'])
                            <div class="aspect-square bg-gray-50/50 rounded-lg"></div>
                        @else
                            @php
                                $dateKey = sprintf('%04d-%02d-%02d', $calendar['year'], $calendar['month'], $day);
                                $hasAppts = isset($calendar['appointments'][$dateKey]);
                                $isToday = $dateKey === now()->format('Y-m-d');
                                $dayCopy = $day;
                            @endphp
                            <div @click="selectedDate = '{{ $dateKey }}'; loadSlots()"
                                class="aspect-square rounded-lg cursor-pointer transition flex flex-col items-center justify-center relative
                                    {{ $isToday ? 'bg-brand-50 border-2 border-brand-400' : 'hover:bg-gray-100 border border-transparent' }}
                                    {{ (request('date') === $dateKey) ? 'ring-2 ring-brand-300' : '' }}">
                                <span class="text-sm font-medium {{ $isToday ? 'text-brand-700' : 'text-gray-700' }}">{{ $day }}</span>
                                @if($hasAppts)
                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500 mt-0.5"></span>
                                @endif
                            </div>
                            @php $day++; @endphp
                        @endif
                    @endfor
                @endfor
            </div>

            {{-- Selected date details --}}
            <div class="mt-5 pt-4 border-t border-gray-100" x-show="selectedDate">
                <h3 class="font-semibold text-gray-900 mb-3" x-text="'{{ __('appointments.appointments_on') }} ' + formatDate(selectedDate)"></h3>

                @php $apptsByDay = $calendar['appointments']; @endphp
                <template x-if="!selectedDate">
                    <div></div>
                </template>
                <template x-if="selectedDate && (!appointmentsOnSelectedDate || appointmentsOnSelectedDate.length === 0)">
                    <p class="text-sm text-gray-400">{{ __('appointments.no_appointments') }}</p>
                </template>
                <div class="space-y-2" x-show="appointmentsOnSelectedDate && appointmentsOnSelectedDate.length > 0">
                    <template x-for="appt in appointmentsOnSelectedDate" :key="appt.id">
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2.5">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full" :style="'background:' + (appt.service?.color || '#3b82f6')"></div>
                                <div>
                                    <div class="text-sm font-medium text-gray-800" x-text="appt.contact?.name || appt.contact?.phone"></div>
                                    <div class="text-xs text-gray-400" x-text="appt.service?.name + ' · ' + formatTime(appt.start_at) + ' - ' + formatTime(appt.end_at)"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <span :class="statusBadgeClass(appt.status)" class="text-[10px] px-2 py-0.5 rounded-full font-medium" x-text="appt.status"></span>
                                <button @click="confirmAction('{{ route('appointments.confirm', '') }}/'+appt.id, 'POST')" x-show="appt.status === 'pending'" class="text-green-600 hover:text-green-800 p-1"><i class="fas fa-check text-xs"></i></button>
                                <button @click="confirmAction('{{ route('appointments.complete', '') }}/'+appt.id, 'POST')" x-show="appt.status === 'confirmed'" class="text-blue-600 hover:text-blue-800 p-1"><i class="fas fa-check-double text-xs"></i></button>
                                <button @click="confirmAction('{{ route('appointments.cancel', '') }}/'+appt.id, 'POST')" x-show="['pending','confirmed'].includes(appt.status)" class="text-red-400 hover:text-red-600 p-1"><i class="fas fa-times text-xs"></i></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Upcoming --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3">{{ __('appointments.upcoming') }}</h2>

            @if($upcoming->isEmpty())
                <p class="text-sm text-gray-400 text-center py-8">{{ __('appointments.no_upcoming') }}</p>
            @else
                <div class="space-y-2 max-h-[480px] overflow-y-auto">
                    @foreach($upcoming as $appt)
                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition border border-gray-100">
                            <div class="w-3 h-3 rounded-full mt-1 flex-shrink-0" style="background: {{ $appt->service->color ?? '#3b82f6' }}"></div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-800 truncate">{{ $appt->contact->name ?? preg_replace('/@.*/', '', $appt->contact->phone) }}</div>
                                <div class="text-[11px] text-gray-500">{{ $appt->service->name ?? 'N/A' }}</div>
                                <div class="text-[11px] text-gray-400">{{ $appt->start_at->format('d M Y, H:i') }}</div>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium mt-1 inline-block {{ WaAppointment::statusBadge($appt->status) }}">{{ $appt->status }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <form action="{{ route('appointments.confirm', $appt) }}" method="POST">
                                    @csrf
                                    <button class="text-green-500 hover:text-green-700 p-0.5" title="Confirm"><i class="fas fa-check text-[10px]"></i></button>
                                </form>
                                <form action="{{ route('appointments.reminder', $appt) }}" method="POST">
                                    @csrf
                                    <button class="text-blue-400 hover:text-blue-600 p-0.5" title="Send Reminder"><i class="fas fa-bell text-[10px]"></i></button>
                                </form>
                                <form action="{{ route('appointments.cancel', $appt) }}" method="POST" onsubmit="return confirm('Cancel?')">
                                    @csrf
                                    <button class="text-red-400 hover:text-red-600 p-0.5" title="Cancel"><i class="fas fa-times text-[10px]"></i></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Services & Availabilities --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-5">
        {{-- Services --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">{{ __('appointments.services') }}</h3>
                <button @click="showServiceModal = true" class="text-xs text-brand-600 font-medium hover:underline"><i class="fas fa-plus text-[10px]"></i> {{ __('common.create') }}</button>
            </div>
            @if($services->isEmpty())
                <p class="text-sm text-gray-400">{{ __('appointments.no_services') }}</p>
            @else
                <div class="space-y-2">
                    @foreach($services as $svc)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full" style="background: {{ $svc->color }}"></div>
                                <div>
                                    <div class="text-sm font-medium text-gray-800">{{ $svc->name }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $svc->duration_minutes }} min · Rp {{ number_format($svc->price, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="editService({{ $svc->id }}, '{{ $svc->name }}', '{{ $svc->description }}', {{ $svc->duration_minutes }}, {{ $svc->price }}, '{{ $svc->color }}')" class="text-gray-400 hover:text-brand-600 p-1"><i class="fas fa-pen text-[10px]"></i></button>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $svc->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $svc->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Availabilities --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">{{ __('appointments.availabilities') }}</h3>
                <button @click="showAvailabilityModal = true" class="text-xs text-brand-600 font-medium hover:underline"><i class="fas fa-plus text-[10px]"></i> {{ __('common.create') }}</button>
            </div>
            @if($availabilities->isEmpty())
                <p class="text-sm text-gray-400">{{ __('appointments.no_availabilities') }}</p>
            @else
                <div class="space-y-1.5 max-h-60 overflow-y-auto">
                    @foreach($availabilities->groupBy('day_of_week') as $dow => $items)
                        <div class="flex items-center gap-3 text-sm">
                            <span class="font-medium text-gray-600 w-20">{{ WaAvailability::dayName($dow) }}</span>
                            <div class="space-x-1">
                                @foreach($items as $item)
                                    <span class="inline-flex items-center gap-1 text-xs bg-gray-100 rounded-lg px-2 py-1
                                        {{ $item->is_active ? 'text-gray-700' : 'text-gray-400 line-through' }}">
                                        {{ $item->start_time }} - {{ $item->end_time }}
                                        <form action="{{ route('availabilities.toggle', $item) }}" method="POST" class="inline">
                                            @csrf
                                            <button class="ml-1 text-gray-400 hover:text-brand-600"><i class="fas fa-toggle-{{ $item->is_active ? 'on' : 'off' }} text-[9px]"></i></button>
                                        </form>
                                        <form action="{{ route('availabilities.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Delete?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-400 hover:text-red-600"><i class="fas fa-times text-[9px]"></i></button>
                                        </form>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Past appointments --}}
    @if($pastAppointments->isNotEmpty())
    <div class="mt-5 bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-900 mb-3">{{ __('appointments.past') }}</h3>
        <div class="space-y-1">
            @foreach($pastAppointments as $pa)
                <div class="flex items-center gap-3 text-sm py-1.5 border-b border-gray-50 last:border-0">
                    <span class="text-gray-400 text-xs">{{ $pa->start_at->format('d/m/Y, H:i') }}</span>
                    <span class="font-medium text-gray-700">{{ $pa->contact->name }}</span>
                    <span class="text-gray-500">{{ $pa->service->name }}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full ml-auto {{ WaAppointment::statusBadge($pa->status) }}">{{ $pa->status }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- BOOKING MODAL --}}
    <div x-show="showBookingModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" x-cloak
        @click.self="showBookingModal = false">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto" @click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('appointments.new_booking') }}</h2>
            <form action="{{ route('appointments.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('appointments.service') }}</label>
                    <select name="service_id" x-model="selectedService" @change="loadSlots()" required
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                        <option value="">{{ __('appointments.select_service') }}</option>
                        @foreach($services->where('is_active', true) as $svc)
                            <option value="{{ $svc->id }}">{{ $svc->name }} ({{ $svc->duration_minutes }} min)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('appointments.date') }}</label>
                    <input type="date" name="date" x-model="selectedDate" @change="loadSlots()"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                </div>
                <div x-show="availableSlots.length > 0">
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('appointments.available_slots') }}</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="slot in availableSlots" :key="slot.datetime">
                            <label class="flex items-center justify-center p-2 rounded-lg border cursor-pointer text-sm hover:border-brand-400 transition"
                                :class="selectedSlot === slot.datetime ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-200 text-gray-600'">
                                <input type="radio" name="datetime" :value="slot.datetime" x-model="selectedSlot" class="sr-only" required>
                                <span x-text="slot.start + ' - ' + slot.end"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <div x-show="selectedDate && !loading && availableSlots.length === 0" class="text-sm text-amber-600 bg-amber-50 rounded-lg p-3">
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ __('appointments.no_slots_available') }}
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('appointments.contact') }}</label>
                    <select name="contact_id" required
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                        <option value="">{{ __('appointments.select_contact') }}</option>
                        @foreach($contacts as $c)
                            <option value="{{ $c->id }}">{{ $c->name !== $c->phone ? $c->name . ' (' . preg_replace('/@.*/', '', $c->phone ?? '') . ')' : preg_replace('/@.*/', '', $c->phone ?? '') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.notes') }}</label>
                    <textarea name="notes" rows="2" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500"></textarea>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="showBookingModal = false"
                        class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                    <button type="submit" :disabled="!selectedSlot"
                        class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700 disabled:opacity-50 transition">{{ __('appointments.book') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- SERVICE MODAL --}}
    <div x-show="showServiceModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" x-cloak
        @click.self="showServiceModal = false">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" @click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-4" x-text="editingServiceId ? 'Edit Service' : '{{ __('appointments.add_service') }}'"></h2>
            <form :action="editingServiceId ? '{{ url('services') }}/' + editingServiceId : '{{ route('services.store') }}'" method="POST" class="space-y-3">
                @csrf
                <template x-if="editingServiceId">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.name') }}</label>
                    <input type="text" name="name" x-model="serviceForm.name" required placeholder="e.g. Konsultasi"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.description') }}</label>
                    <input type="text" name="description" x-model="serviceForm.description" placeholder="Brief description"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('appointments.duration_mins') }}</label>
                        <input type="number" name="duration_minutes" x-model="serviceForm.duration" min="5" max="480" required
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.price') }}</label>
                        <input type="number" name="price" x-model="serviceForm.price" step="0.01" min="0"
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('common.color') }}</label>
                    <input type="color" name="color" x-model="serviceForm.color"
                        class="w-12 h-9 rounded-lg border border-gray-300 cursor-pointer">
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="showServiceModal = false; editingServiceId = null"
                        class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                    <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
                    <template x-if="editingServiceId">
                        <a :href="'{{ url('services') }}/' + editingServiceId" onclick="event.preventDefault(); if(confirm('Delete this service?')){ let f = document.createElement('form'); f.method='POST'; f.action=this.href; f.innerHTML='@csrf @method('DELETE')'; document.body.append(f); f.submit(); }"
                            class="px-3 py-2.5 bg-red-50 text-red-600 rounded-xl text-sm font-medium hover:bg-red-100"><i class="fas fa-trash"></i></a>
                    </template>
                </div>
            </form>
        </div>
    </div>

    {{-- AVAILABILITY MODAL --}}
    <div x-show="showAvailabilityModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" x-cloak
        @click.self="showAvailabilityModal = false">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl" @click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('appointments.add_availability') }}</h2>
            <form action="{{ route('availabilities.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('appointments.day') }}</label>
                    <select name="day_of_week" required
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                        @for($i = 0; $i < 7; $i++)
                            <option value="{{ $i }}">{{ WaAvailability::dayName($i) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('appointments.start_time') }}</label>
                        <input type="time" name="start_time" value="09:00" required
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('appointments.end_time') }}</label>
                        <input type="time" name="end_time" value="17:00" required
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="showAvailabilityModal = false"
                        class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                    <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $apptMap = [];
    foreach ($calendar['appointments'] as $dateKey => $list) {
        $apptMap[$dateKey] = $list->map(fn($a) => [
            'id' => $a->id,
            'status' => $a->status,
            'start_at' => $a->start_at->toIso8601String(),
            'end_at' => $a->end_at->toIso8601String(),
            'service' => $a->service ? ['id' => $a->service->id, 'name' => $a->service->name, 'color' => $a->service->color] : null,
            'contact' => $a->contact ? ['id' => $a->contact->id, 'name' => $a->contact->name, 'phone' => $a->contact->phone] : null,
        ])->values()->toArray();
    }
@endphp
<script>
function appointmentManager() {
    return {
        showBookingModal: false,
        showServiceModal: false,
        showAvailabilityModal: false,
        selectedDate: '{{ request('date', now()->format('Y-m-d')) }}',
        selectedService: '',
        selectedSlot: '',
        availableSlots: [],
        loading: false,
        editingServiceId: null,
        serviceForm: { name: '', description: '', duration: 30, price: 0, color: '#3b82f6' },
        appointmentMap: @json($apptMap),

        get appointmentsOnSelectedDate() {
            return this.appointmentMap[this.selectedDate] || [];
        },

        async loadSlots() {
            if (!this.selectedDate) return;
            this.loading = true;
            this.availableSlots = [];
            this.selectedSlot = '';
            try {
                const params = new URLSearchParams({ date: this.selectedDate });
                if (this.selectedService) params.set('service_id', this.selectedService);
                const res = await fetch('{{ route('api.appointments.slots') }}?' + params.toString());
                const data = await res.json();
                this.availableSlots = data.slots || [];
            } catch (e) {}
            this.loading = false;
        },

        editService(id, name, desc, dur, price, color) {
            this.editingServiceId = id;
            this.serviceForm = { name, description: desc || '', duration: dur, price, color };
            this.showServiceModal = true;
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        },

        formatTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },

        statusBadgeClass(status) {
            const map = { pending: 'bg-yellow-100 text-yellow-800', confirmed: 'bg-green-100 text-green-800', cancelled: 'bg-red-100 text-red-800', completed: 'bg-blue-100 text-blue-800' };
            return map[status] || 'bg-gray-100 text-gray-600';
        },

        confirmAction(url, method) {
            if (confirm('{{ __('common.confirm') }}?')) {
                const f = document.createElement('form');
                f.method = 'POST'; f.action = url;
                f.innerHTML = '@csrf';
                document.body.append(f); f.submit();
            }
        }
    };
}
</script>
@endpush
