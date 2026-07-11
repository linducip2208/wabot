<?php $__env->startSection('title', 'Webhook — WABot'); ?>
<?php $__env->startSection('content'); ?>

<?php
$eventOptions = [
    'message.received'      => ['label' => __('webhooks.event_received'), 'icon' => 'fa-inbox', 'cls' => 'bg-sky-50 text-sky-700 border-sky-200'],
    'message.sent'          => ['label' => __('webhooks.event_sent'), 'icon' => 'fa-paper-plane', 'cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
    'session.connected'     => ['label' => __('webhooks.event_connected'), 'icon' => 'fa-plug', 'cls' => 'bg-green-50 text-green-700 border-green-200'],
    'session.disconnected'  => ['label' => __('webhooks.event_disconnected'), 'icon' => 'fa-plug-circle-xmark', 'cls' => 'bg-rose-50 text-rose-700 border-rose-200'],
    'campaign.completed'    => ['label' => __('webhooks.event_campaign'), 'icon' => 'fa-bullhorn', 'cls' => 'bg-amber-50 text-amber-700 border-amber-200'],
];
?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Webhook</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e($webhooks->count()); ?> endpoint · <?php echo e(__('webhooks.subtitle')); ?></p>
    </div>
    <button onclick="openCreate()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?> Webhook
    </button>
</div>

<div class="grid gap-3 mb-8">
    <?php $__empty_1 = true; $__currentLoopData = $webhooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 bg-violet-50">
                    <i class="fas fa-bolt text-violet-500"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="font-semibold text-sm text-gray-900 truncate"><?php echo e($w->name); ?></span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium <?php echo e($w->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'); ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?php echo e($w->is_active ? 'bg-emerald-500' : 'bg-gray-400'); ?>"></span>
                            <?php echo e($w->is_active ? __('common.active') : __('common.inactive')); ?>

                        </span>
                        <?php if($w->last_triggered_at): ?>
                        <span class="text-[11px] text-gray-400"><i class="fas fa-clock-rotate-left mr-0.5"></i> <?php echo e($w->last_triggered_at->diffForHumans()); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="font-mono text-xs text-gray-700 bg-gray-50 px-2.5 py-1.5 rounded-lg break-all mb-2"><?php echo e($w->url); ?></div>
                    <div class="flex flex-wrap gap-1.5">
                        <?php $__currentLoopData = ($w->events ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $em = $eventOptions[$ev] ?? null; ?>
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-md border <?php echo e($em['cls'] ?? 'bg-gray-100 text-gray-600 border-gray-200'); ?>">
                                <i class="fas <?php echo e($em['icon'] ?? 'fa-circle'); ?> text-[9px]"></i> <?php echo e($em['label'] ?? $ev); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <form method="POST" action="<?php echo e(route('webhooks.test', $w)); ?>">
                    <?php echo csrf_field(); ?>
                    <button class="p-1.5 rounded-lg hover:bg-sky-50 text-gray-400 hover:text-sky-600" title="<?php echo e(__('webhooks.send_test')); ?>"><i class="fas fa-vial text-xs"></i></button>
                </form>
                <button onclick="editWebhook(<?php echo e($w->id); ?>)"
                    class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                <form method="POST" action="<?php echo e(route('webhooks.destroy', $w)); ?>" onsubmit="return confirm('<?php echo e(__('webhooks.delete_confirm')); ?>')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-bolt text-2xl text-gray-400"></i>
        </div>
        <p class="text-gray-500 font-medium mb-1"><?php echo e(__('webhooks.empty_title')); ?></p>
        <p class="text-sm text-gray-400 mb-4"><?php echo e(__('webhooks.empty_desc')); ?></p>
        <button onclick="openCreate()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
            <i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?> Webhook
        </button>
    </div>
    <?php endif; ?>
</div>


<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 flex items-center gap-2">
        <i class="fas fa-list-ul text-gray-400 text-sm"></i>
        <h2 class="text-sm font-bold text-gray-900"><?php echo e(__('webhooks.recent_logs')); ?></h2>
        <span class="text-xs text-gray-400">(<?php echo e($recentLogs->count()); ?> entri)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400 border-b border-gray-100">
                    <th class="px-4 py-2.5"><?php echo e(__('common.time')); ?></th>
                    <th class="px-4 py-2.5"><?php echo e(__('webhooks.webhook')); ?></th>
                    <th class="px-4 py-2.5"><?php echo e(__('webhooks.event')); ?></th>
                    <th class="px-4 py-2.5"><?php echo e(__('common.status')); ?></th>
                    <th class="px-4 py-2.5"><?php echo e(__('webhooks.description_label')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $code = $log->response_code;
                    $ok = $code !== null && $code >= 200 && $code < 300;
                ?>
                <tr class="hover:bg-gray-50/60">
                    <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap text-xs"><?php echo e($log->created_at?->format('d M H:i:s')); ?></td>
                    <td class="px-4 py-2.5 text-gray-700"><?php echo e($log->webhook?->name ?? '—'); ?></td>
                    <td class="px-4 py-2.5"><span class="font-mono text-xs text-gray-600"><?php echo e($log->event); ?></span></td>
                    <td class="px-4 py-2.5">
                        <?php if($code === null): ?>
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200"><i class="fas fa-xmark text-[9px]"></i> <?php echo e(__('common.failed')); ?></span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 text-xs font-mono font-medium px-2 py-0.5 rounded-md border <?php echo e($ok ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'); ?>">
                                <i class="fas <?php echo e($ok ? 'fa-check' : 'fa-triangle-exclamation'); ?> text-[9px]"></i> <?php echo e($code); ?>

                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs max-w-xs truncate" title="<?php echo e($log->error ?: $log->response_body); ?>"><?php echo e($log->error ?: ($log->response_body ?: '—')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">
                        <i class="fas fa-inbox text-2xl mb-2 block opacity-40"></i> <?php echo e(__('webhooks.empty_logs')); ?>

                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div id="whModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="whModalTitle"><?php echo e(__('common.create')); ?> Webhook</h2>
        <form method="POST" action="<?php echo e(route('webhooks.store')); ?>" class="space-y-3" id="whForm">
            <?php echo csrf_field(); ?>
            <div id="whMethodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" placeholder="CRM Integrasi" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('webhooks.url_endpoint')); ?></label>
                <input type="url" name="url" placeholder="https://app.contoh.com/webhook" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1.5 block"><?php echo e(__('webhooks.events_to_send')); ?></label>
                <div class="grid grid-cols-1 gap-1.5">
                    <?php $__currentLoopData = $eventOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-2.5 px-3 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="events[]" value="<?php echo e($key); ?>" class="wh-event rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <i class="fas <?php echo e($opt['icon']); ?> text-gray-400 text-xs w-4 text-center"></i>
                        <span class="text-sm text-gray-700"><?php echo e($opt['label']); ?></span>
                        <span class="ml-auto font-mono text-[11px] text-gray-400"><?php echo e($key); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.status')); ?></label>
                <select name="is_active" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="1"><?php echo e(__('common.active')); ?></option>
                    <option value="0"><?php echo e(__('common.inactive')); ?></option>
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="closeModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>

<?php
$webhooksJson = $webhooks->keyBy('id')->map(function($w) {
    return ['name' => $w->name, 'url' => $w->url, 'events' => $w->events ?? [], 'is_active' => $w->is_active];
});
?>

<?php $__env->startPush('scripts'); ?>
<script>
const webhooksData = <?php echo json_encode($webhooksJson); ?>;

function closeModal() {
    document.getElementById('whModal').classList.add('hidden');
}

function setEvents(events) {
    document.querySelectorAll('.wh-event').forEach(cb => {
        cb.checked = events.includes(cb.value);
    });
}

function openCreate() {
    const f = document.getElementById('whForm');
    document.getElementById('whModalTitle').textContent = '<?php echo e(__('common.create')); ?> Webhook';
    f.action = '<?php echo e(route('webhooks.store')); ?>';
    f.querySelector('input[name="name"]').value = '';
    f.querySelector('input[name="url"]').value = '';
    f.querySelector('select[name="is_active"]').value = '1';
    setEvents([]);
    document.getElementById('whMethodField').innerHTML = '';
    document.getElementById('whModal').classList.remove('hidden');
}

function editWebhook(id) {
    const data = webhooksData[id];
    if (!data) return;
    const f = document.getElementById('whForm');
    document.getElementById('whModalTitle').textContent = '<?php echo e(__('webhooks.edit_webhook')); ?>';
    f.action = '/webhooks/' + id;
    f.querySelector('input[name="name"]').value = data.name;
    f.querySelector('input[name="url"]').value = data.url;
    f.querySelector('select[name="is_active"]').value = data.is_active ? '1' : '0';
    setEvents(data.events || []);
    document.getElementById('whMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('whModal').classList.remove('hidden');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\webhooks\index.blade.php ENDPATH**/ ?>