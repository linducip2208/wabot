<?php $__env->startSection('title', __('appointments.title') . ' — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto" x-data="appointmentManager()">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900"><?php echo e(__('appointments.title')); ?></h1>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('appointments.subtitle')); ?></p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button @click="showBookingModal = true; loadSlots()"
                class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
                <i class="fas fa-plus text-xs"></i> <?php echo e(__('appointments.new_booking')); ?>

            </button>
            <button @click="showServiceModal = true"
                class="bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fas fa-cog text-xs"></i> <?php echo e(__('appointments.manage_services')); ?>

            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900"><?php echo e($calendar['monthName']); ?> <?php echo e($calendar['year']); ?></h2>
                <div class="flex gap-2">
                    <a href="?month=<?php echo e($calendar['prevMonth']); ?>" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition">
                        <i class="fas fa-chevron-left text-sm"></i>
                    </a>
                    <a href="?month=<?php echo e(now()->format('Y-m')); ?>" class="px-3 py-1.5 rounded-lg hover:bg-gray-100 text-sm font-medium text-gray-600 transition">
                        <?php echo e(__('common.this_month')); ?>

                    </a>
                    <a href="?month=<?php echo e($calendar['nextMonth']); ?>" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-1">
                <?php $__currentLoopData = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="text-center text-[11px] font-semibold text-gray-500 uppercase py-1.5"><?php echo e($label); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php $day = 1; ?>
                <?php for($week = 0; $week < 6; $week++): ?>
                    <?php for($dow = 0; $dow < 7; $dow++): ?>
                        <?php if(($week === 0 && $dow < $calendar['firstDayOfWeek']) || $day > $calendar['daysInMonth']): ?>
                            <div class="aspect-square bg-gray-50/50 rounded-lg"></div>
                        <?php else: ?>
                            <?php
                                $dateKey = sprintf('%04d-%02d-%02d', $calendar['year'], $calendar['month'], $day);
                                $hasAppts = isset($calendar['appointments'][$dateKey]);
                                $isToday = $dateKey === now()->format('Y-m-d');
                                $dayCopy = $day;
                            ?>
                            <div @click="selectedDate = '<?php echo e($dateKey); ?>'; loadSlots()"
                                class="aspect-square rounded-lg cursor-pointer transition flex flex-col items-center justify-center relative
                                    <?php echo e($isToday ? 'bg-brand-50 border-2 border-brand-400' : 'hover:bg-gray-100 border border-transparent'); ?>

                                    <?php echo e((request('date') === $dateKey) ? 'ring-2 ring-brand-300' : ''); ?>">
                                <span class="text-sm font-medium <?php echo e($isToday ? 'text-brand-700' : 'text-gray-700'); ?>"><?php echo e($day); ?></span>
                                <?php if($hasAppts): ?>
                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500 mt-0.5"></span>
                                <?php endif; ?>
                            </div>
                            <?php $day++; ?>
                        <?php endif; ?>
                    <?php endfor; ?>
                <?php endfor; ?>
            </div>

            
            <div class="mt-5 pt-4 border-t border-gray-100" x-show="selectedDate">
                <h3 class="font-semibold text-gray-900 mb-3" x-text="'<?php echo e(__('appointments.appointments_on')); ?> ' + formatDate(selectedDate)"></h3>

                <?php $apptsByDay = $calendar['appointments']; ?>
                <template x-if="!selectedDate">
                    <div></div>
                </template>
                <template x-if="selectedDate && (!appointmentsOnSelectedDate || appointmentsOnSelectedDate.length === 0)">
                    <p class="text-sm text-gray-400"><?php echo e(__('appointments.no_appointments')); ?></p>
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
                                <button @click="confirmAction('/appointments/'+appt.id+'/confirm', 'POST')" x-show="appt.status === 'pending'" class="text-green-600 hover:text-green-800 p-1"><i class="fas fa-check text-xs"></i></button>
                                <button @click="confirmAction('/appointments/'+appt.id+'/complete', 'POST')" x-show="appt.status === 'confirmed'" class="text-blue-600 hover:text-blue-800 p-1"><i class="fas fa-check-double text-xs"></i></button>
                                <button @click="confirmAction('/appointments/'+appt.id+'/cancel', 'POST')" x-show="['pending','confirmed'].includes(appt.status)" class="text-red-400 hover:text-red-600 p-1"><i class="fas fa-times text-xs"></i></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3"><?php echo e(__('appointments.upcoming')); ?></h2>

            <?php if($upcoming->isEmpty()): ?>
                <p class="text-sm text-gray-400 text-center py-8"><?php echo e(__('appointments.no_upcoming')); ?></p>
            <?php else: ?>
                <div class="space-y-2 max-h-[480px] overflow-y-auto">
                    <?php $__currentLoopData = $upcoming; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-gray-50 transition border border-gray-100">
                            <div class="w-3 h-3 rounded-full mt-1 flex-shrink-0" style="background: <?php echo e($appt->service->color ?? '#3b82f6'); ?>"></div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-800 truncate"><?php echo e($appt->contact->name ?? preg_replace('/@.*/', '', $appt->contact->phone)); ?></div>
                                <div class="text-[11px] text-gray-500"><?php echo e($appt->service->name ?? 'N/A'); ?></div>
                                <div class="text-[11px] text-gray-400"><?php echo e($appt->start_at->format('d M Y, H:i')); ?></div>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium mt-1 inline-block <?php echo e(App\Models\WaAppointment::statusBadge($appt->status)); ?>"><?php echo e($appt->status); ?></span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <form action="<?php echo e(route('appointments.confirm', $appt)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button class="text-green-500 hover:text-green-700 p-0.5" title="Confirm"><i class="fas fa-check text-[10px]"></i></button>
                                </form>
                                <form action="<?php echo e(route('appointments.reminder', $appt)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button class="text-blue-400 hover:text-blue-600 p-0.5" title="Send Reminder"><i class="fas fa-bell text-[10px]"></i></button>
                                </form>
                                <form action="<?php echo e(route('appointments.cancel', $appt)); ?>" method="POST" onsubmit="return confirm('Cancel?')">
                                    <?php echo csrf_field(); ?>
                                    <button class="text-red-400 hover:text-red-600 p-0.5" title="Cancel"><i class="fas fa-times text-[10px]"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-5">
        
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900"><?php echo e(__('appointments.services')); ?></h3>
                <button @click="showServiceModal = true" class="text-xs text-brand-600 font-medium hover:underline"><i class="fas fa-plus text-[10px]"></i> <?php echo e(__('common.create')); ?></button>
            </div>
            <?php if($services->isEmpty()): ?>
                <p class="text-sm text-gray-400"><?php echo e(__('appointments.no_services')); ?></p>
            <?php else: ?>
                <div class="space-y-2">
                    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $svc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full" style="background: <?php echo e($svc->color); ?>"></div>
                                <div>
                                    <div class="text-sm font-medium text-gray-800"><?php echo e($svc->name); ?></div>
                                    <div class="text-[11px] text-gray-400"><?php echo e($svc->duration_minutes); ?> min · Rp <?php echo e(number_format($svc->price, 0, ',', '.')); ?></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="editService(<?php echo e($svc->id); ?>, '<?php echo e($svc->name); ?>', '<?php echo e($svc->description); ?>', <?php echo e($svc->duration_minutes); ?>, <?php echo e($svc->price); ?>, '<?php echo e($svc->color); ?>')" class="text-gray-400 hover:text-brand-600 p-1"><i class="fas fa-pen text-[10px]"></i></button>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full <?php echo e($svc->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>"><?php echo e($svc->is_active ? 'Active' : 'Inactive'); ?></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900"><?php echo e(__('appointments.availabilities')); ?></h3>
                <button @click="showAvailabilityModal = true" class="text-xs text-brand-600 font-medium hover:underline"><i class="fas fa-plus text-[10px]"></i> <?php echo e(__('common.create')); ?></button>
            </div>
            <?php if($availabilities->isEmpty()): ?>
                <p class="text-sm text-gray-400"><?php echo e(__('appointments.no_availabilities')); ?></p>
            <?php else: ?>
                <div class="space-y-1.5 max-h-60 overflow-y-auto">
                    <?php $__currentLoopData = $availabilities->groupBy('day_of_week'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dow => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3 text-sm">
                            <span class="font-medium text-gray-600 w-20"><?php echo e(App\Models\WaAvailability::dayName($dow)); ?></span>
                            <div class="space-x-1">
                                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-flex items-center gap-1 text-xs bg-gray-100 rounded-lg px-2 py-1
                                        <?php echo e($item->is_active ? 'text-gray-700' : 'text-gray-400 line-through'); ?>">
                                        <?php echo e($item->start_time); ?> - <?php echo e($item->end_time); ?>

                                        <form action="<?php echo e(route('availabilities.toggle', $item)); ?>" method="POST" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button class="ml-1 text-gray-400 hover:text-brand-600"><i class="fas fa-toggle-<?php echo e($item->is_active ? 'on' : 'off'); ?> text-[9px]"></i></button>
                                        </form>
                                        <form action="<?php echo e(route('availabilities.destroy', $item)); ?>" method="POST" class="inline" onsubmit="return confirm('Delete?')">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button class="text-red-400 hover:text-red-600"><i class="fas fa-times text-[9px]"></i></button>
                                        </form>
                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if($pastAppointments->isNotEmpty()): ?>
    <div class="mt-5 bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-900 mb-3"><?php echo e(__('appointments.past')); ?></h3>
        <div class="space-y-1">
            <?php $__currentLoopData = $pastAppointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-3 text-sm py-1.5 border-b border-gray-50 last:border-0">
                    <span class="text-gray-400 text-xs"><?php echo e($pa->start_at->format('d/m/Y, H:i')); ?></span>
                    <span class="font-medium text-gray-700"><?php echo e($pa->contact->name); ?></span>
                    <span class="text-gray-500"><?php echo e($pa->service->name); ?></span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full ml-auto <?php echo e(App\Models\WaAppointment::statusBadge($pa->status)); ?>"><?php echo e($pa->status); ?></span>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    
    <div x-show="showBookingModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" x-cloak
        @click.self="showBookingModal = false">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto" @click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('appointments.new_booking')); ?></h2>
            <form action="<?php echo e(route('appointments.store')); ?>" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('appointments.service')); ?></label>
                    <select name="service_id" x-model="selectedService" @change="loadSlots()" required
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                        <option value=""><?php echo e(__('appointments.select_service')); ?></option>
                        <?php $__currentLoopData = $services->where('is_active', true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $svc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($svc->id); ?>"><?php echo e($svc->name); ?> (<?php echo e($svc->duration_minutes); ?> min)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('appointments.date')); ?></label>
                    <input type="date" name="date" x-model="selectedDate" @change="loadSlots()"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                </div>
                <div x-show="availableSlots.length > 0">
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('appointments.available_slots')); ?></label>
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
                    <i class="fas fa-exclamation-triangle mr-1"></i> <?php echo e(__('appointments.no_slots_available')); ?>

                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('appointments.contact')); ?></label>
                    <select name="contact_id" required
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                        <option value=""><?php echo e(__('appointments.select_contact')); ?></option>
                        <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>"><?php echo e($c->name !== $c->phone ? $c->name . ' (' . preg_replace('/@.*/', '', $c->phone ?? '') . ')' : preg_replace('/@.*/', '', $c->phone ?? '')); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.notes')); ?></label>
                    <textarea name="notes" rows="2" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500"></textarea>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="showBookingModal = false"
                        class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                    <button type="submit" :disabled="!selectedSlot"
                        class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700 disabled:opacity-50 transition"><?php echo e(__('appointments.book')); ?></button>
                </div>
            </form>
        </div>
    </div>

    
    <div x-show="showServiceModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" x-cloak
        @click.self="showServiceModal = false">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" @click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-4" x-text="editingServiceId ? 'Edit Service' : '<?php echo e(__('appointments.add_service')); ?>'"></h2>
            <form :action="editingServiceId ? '<?php echo e(url('services')); ?>/' + editingServiceId : '<?php echo e(route('services.store')); ?>'" method="POST" class="space-y-3">
                <?php echo csrf_field(); ?>
                <template x-if="editingServiceId">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.name')); ?></label>
                    <input type="text" name="name" x-model="serviceForm.name" required placeholder="e.g. Konsultasi"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.description')); ?></label>
                    <input type="text" name="description" x-model="serviceForm.description" placeholder="Brief description"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('appointments.duration_mins')); ?></label>
                        <input type="number" name="duration_minutes" x-model="serviceForm.duration" min="5" max="480" required
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.price')); ?></label>
                        <input type="number" name="price" x-model="serviceForm.price" step="0.01" min="0"
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.color')); ?></label>
                    <input type="color" name="color" x-model="serviceForm.color"
                        class="w-12 h-9 rounded-lg border border-gray-300 cursor-pointer">
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="showServiceModal = false; editingServiceId = null"
                        class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                    <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
                    <template x-if="editingServiceId">
                        <a :href="'<?php echo e(url('services')); ?>/' + editingServiceId" onclick="event.preventDefault(); if(confirm('Delete this service?')){ let f = document.createElement('form'); f.method='POST'; f.action=this.href; f.innerHTML='<?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>'; document.body.append(f); f.submit(); }"
                            class="px-3 py-2.5 bg-red-50 text-red-600 rounded-xl text-sm font-medium hover:bg-red-100"><i class="fas fa-trash"></i></a>
                    </template>
                </div>
            </form>
        </div>
    </div>

    
    <div x-show="showAvailabilityModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" x-cloak
        @click.self="showAvailabilityModal = false">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl" @click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('appointments.add_availability')); ?></h2>
            <form action="<?php echo e(route('availabilities.store')); ?>" method="POST" class="space-y-3">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('appointments.day')); ?></label>
                    <select name="day_of_week" required
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                        <?php for($i = 0; $i < 7; $i++): ?>
                            <option value="<?php echo e($i); ?>"><?php echo e(App\Models\WaAvailability::dayName($i)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('appointments.start_time')); ?></label>
                        <input type="time" name="start_time" value="09:00" required
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('appointments.end_time')); ?></label>
                        <input type="time" name="end_time" value="17:00" required
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="button" @click="showAvailabilityModal = false"
                        class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                    <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php
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
?>
<script>
function appointmentManager() {
    return {
        showBookingModal: false,
        showServiceModal: false,
        showAvailabilityModal: false,
        selectedDate: '<?php echo e(request('date', now()->format('Y-m-d'))); ?>',
        selectedService: '',
        selectedSlot: '',
        availableSlots: [],
        loading: false,
        editingServiceId: null,
        serviceForm: { name: '', description: '', duration: 30, price: 0, color: '#3b82f6' },
        appointmentMap: <?php echo json_encode($apptMap, 15, 512) ?>,

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
                const res = await fetch('<?php echo e(route('api.appointments.slots')); ?>?' + params.toString());
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
            if (confirm('<?php echo e(__('common.confirm')); ?>?')) {
                const f = document.createElement('form');
                f.method = 'POST'; f.action = url;
                f.innerHTML = '<?php echo csrf_field(); ?>';
                document.body.append(f); f.submit();
            }
        }
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\appointments\index.blade.php ENDPATH**/ ?>