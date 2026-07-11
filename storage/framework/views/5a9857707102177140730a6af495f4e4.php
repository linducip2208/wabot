<?php $__env->startSection('title', __('recurrings.index_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('recurrings.heading')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('recurrings.subtitle')); ?></p>
    </div>
    <button onclick="openCreateModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?>

    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <?php $targetLabels = ['all' => __('common.all'), 'group' => 'Group', 'numbers' => __('recurrings.specific_numbers')]; ?>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-4 py-3"><?php echo e(__('common.name')); ?></th>
                <th class="px-4 py-3"><?php echo e(__('recurrings.recurrence')); ?></th>
                <th class="px-4 py-3"><?php echo e(__('common.time')); ?></th>
                <th class="px-4 py-3"><?php echo e(__('recurrings.channel')); ?></th>
                <th class="px-4 py-3"><?php echo e(__('recurrings.target')); ?></th>
                <th class="px-4 py-3"><?php echo e(__('common.status')); ?></th>
                <th class="px-4 py-3"><?php echo e(__('recurrings.last_sent')); ?></th>
                <th class="px-4 py-3 w-20"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($s->name); ?></td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        <?php echo e($s->recurrence === 'once' ? 'bg-gray-100 text-gray-700' : ''); ?>

                        <?php echo e($s->recurrence === 'daily' ? 'bg-blue-50 text-blue-700' : ''); ?>

                        <?php echo e($s->recurrence === 'weekly' ? 'bg-violet-50 text-violet-700' : ''); ?>

                        <?php echo e($s->recurrence === 'monthly' ? 'bg-amber-50 text-amber-700' : ''); ?>">
                        <?php echo e(['once' => __('recurrings.once'), 'daily' => __('recurrings.daily'), 'weekly' => __('recurrings.weekly'), 'monthly' => __('recurrings.monthly')][$s->recurrence]); ?>

                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600"><?php echo e($s->time ?? '-'); ?></td>
                <td class="px-4 py-3">
                    <?php
                        $ch = $s->channel ?? 'whatsapp';
                        $chBadge = ['whatsapp' => 'bg-emerald-50 text-emerald-700', 'meta' => 'bg-blue-50 text-blue-700', 'telegram' => 'bg-sky-50 text-sky-700'][$ch] ?? 'bg-gray-50 text-gray-700';
                        $chName = ['whatsapp' => 'WA', 'meta' => 'Meta', 'telegram' => 'TG'][$ch] ?? 'WA';
                    ?>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium <?php echo e($chBadge); ?>"><?php echo e($chName); ?></span>
                    <span class="text-xs text-gray-500 ml-1"><?php echo e($s->session?->name ?? $s->metaAccount?->name ?? $s->telegramAccount?->name ?? '-'); ?></span>
                </td>
                <td class="px-4 py-3 text-gray-600"><?php echo e($targetLabels[$s->target_type]); ?></td>
                <td class="px-4 py-3">
                    <form method="POST" action="<?php echo e(route('recurrings.toggle', $s)); ?>">
                        <?php echo csrf_field(); ?>
                        <button class="text-xs font-medium px-2 py-0.5 rounded-full <?php echo e($s->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'); ?>">
                            <?php echo e($s->is_active ? __('common.active') : __('common.inactive')); ?>

                        </button>
                    </form>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400"><?php echo e($s->last_sent_at?->diffForHumans() ?? '-'); ?></td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1">
                        <button onclick="editSchedule(<?php echo e($s->id); ?>, '<?php echo e(addslashes($s->name)); ?>', '<?php echo e($s->recurrence); ?>', '<?php echo e($s->time); ?>', '<?php echo e($s->session_id ?? ''); ?>', '<?php echo e($s->target_type); ?>', '<?php echo e(addslashes($s->message)); ?>', '<?php echo e($s->channel ?? 'whatsapp'); ?>', '<?php echo e($s->meta_account_id ?? ''); ?>', '<?php echo e($s->telegram_account_id ?? ''); ?>')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                        <form method="POST" action="<?php echo e(route('recurrings.destroy', $s)); ?>" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="8" class="px-4 py-16 text-center text-gray-500"><?php echo e(__('recurrings.empty')); ?> <button onclick="openCreateModal()" class="text-brand-600 hover:underline"><?php echo e(__('recurrings.empty_cta')); ?></button></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<div id="scheduleModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="modalTitle"><?php echo e(__('recurrings.create_modal_title')); ?></h2>
        <form method="POST" action="<?php echo e(route('recurrings.store')); ?>" class="space-y-3" id="scheduleForm" x-data="{ schChannel: 'whatsapp' }">
            <?php echo csrf_field(); ?>
            <div id="methodField"></div>
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?></label>
                    <input type="text" name="name" id="fName" placeholder="Welcome Message" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Channel</label>
                    <select name="channel" x-model="schChannel" id="fChannel" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="whatsapp">WhatsApp (Baileys)</option>
                        <option value="meta">WhatsApp Cloud (Meta)</option>
                        <option value="telegram">Telegram</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('recurrings.recurrence')); ?></label>
                    <select name="recurrence" id="fRecurrence" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="once"><?php echo e(__('recurrings.once')); ?></option>
                        <option value="daily"><?php echo e(__('recurrings.daily')); ?></option>
                        <option value="weekly"><?php echo e(__('recurrings.weekly')); ?></option>
                        <option value="monthly"><?php echo e(__('recurrings.monthly')); ?></option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('recurrings.time_label')); ?></label>
                    <input type="time" name="time" id="fTime" value="08:00" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div x-show="schChannel === 'whatsapp'">
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.session')); ?></label>
                    <select name="session_id" id="fSessionId" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value=""><?php echo e(__('recurrings.auto')); ?></option>
                        <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div x-show="schChannel === 'meta'">
                    <label class="text-xs font-medium text-gray-500">Meta Account</label>
                    <select name="meta_account_id" id="fMetaAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Meta Account</option>
                        <?php $__currentLoopData = $metaAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m->id); ?>"><?php echo e($m->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div x-show="schChannel === 'telegram'">
                    <label class="text-xs font-medium text-gray-500">Telegram Account</label>
                    <select name="telegram_account_id" id="fTelegramAccountId" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Pilih Telegram Account</option>
                        <?php $__currentLoopData = $telegramAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($t->id); ?>"><?php echo e($t->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('recurrings.target')); ?></label>
                    <select name="target_type" id="fTargetType" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <option value="all"><?php echo e(__('common.all')); ?> <?php echo e(__('common.contact')); ?></option>
                        <option value="numbers"><?php echo e(__('recurrings.specific_numbers')); ?></option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.message')); ?> <span class="text-gray-400"><?php echo e(__('recurrings.spintax_hint')); ?>, {'{name}'} = <?php echo e(__('common.name')); ?>)</span></label>
                <textarea name="message" id="fMessage" rows="3" required placeholder="Halo {name}! {Selamat datang|Hai, apa kabar?}" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('scheduleModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').textContent = '<?php echo e(__('recurrings.create_modal_title')); ?>';
    const f = document.getElementById('scheduleForm');
    f.action = '<?php echo e(route('recurrings.store')); ?>';
    f.reset();
    document.getElementById('fTime').value = '08:00';
    document.getElementById('fChannel').value = 'whatsapp';
    document.getElementById('fChannel').dispatchEvent(new Event('input', { bubbles: true }));
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('scheduleModal').classList.remove('hidden');
}

function editSchedule(id, name, recurrence, time, sessionId, targetType, message, channel, metaAccountId, telegramAccountId) {
    document.getElementById('modalTitle').textContent = '<?php echo e(__('recurrings.edit_modal_title')); ?>';
    const f = document.getElementById('scheduleForm');
    f.action = '/recurrings/' + id;
    document.getElementById('fName').value = name;
    document.getElementById('fRecurrence').value = recurrence;
    document.getElementById('fTime').value = time || '08:00';
    document.getElementById('fChannel').value = channel || 'whatsapp';
    document.getElementById('fChannel').dispatchEvent(new Event('input', { bubbles: true }));
    document.getElementById('fSessionId').value = sessionId || '';
    document.getElementById('fMetaAccountId').value = metaAccountId || '';
    document.getElementById('fTelegramAccountId').value = telegramAccountId || '';
    document.getElementById('fTargetType').value = targetType;
    document.getElementById('fMessage').value = message;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('scheduleModal').classList.remove('hidden');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\recurrings\index.blade.php ENDPATH**/ ?>