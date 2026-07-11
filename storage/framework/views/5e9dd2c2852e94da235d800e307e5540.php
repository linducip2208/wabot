<?php $__env->startSection('title', __('common.message') . ' ' . __('common.sent') . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('common.message')); ?> <?php echo e(__('common.sent')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e($messages->total()); ?> <?php echo e(__('common.message')); ?> <?php echo e(__('messages.outgoing_count')); ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('messages.received')); ?>" class="bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
            <i class="fas fa-inbox mr-1"></i> <?php echo e(__('messages.inbox')); ?>

        </a>
        <a href="<?php echo e(route('messages.sent')); ?>" class="bg-blue-600 text-white px-3 py-2 rounded-xl text-sm font-medium">
            <i class="fas fa-paper-plane mr-1"></i> <?php echo e(__('common.sent')); ?>

        </a>
        <a href="<?php echo e(route('messages.queue')); ?>" class="bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
            <i class="fas fa-clock mr-1"></i> <?php echo e(__('messages.queue')); ?>

        </a>
    </div>
</div>


<div class="flex gap-2 mb-4 flex-wrap items-center">
    <form method="GET" action="<?php echo e(route('messages.search')); ?>" class="flex gap-2 flex-1">
        <input type="hidden" name="direction" value="out">
        <input type="text" name="q" placeholder="Cari pesan, nomor, atau nama kontak..." value="<?php echo e(request('q')); ?>"
            class="flex-1 rounded-xl border border-gray-300 px-3 py-2 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        <button type="submit" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-xs font-medium hover:bg-brand-700">
            <i class="fas fa-search mr-1"></i> Cari
        </button>
        <?php if(request('q')): ?>
        <a href="<?php echo e(route('messages.sent')); ?>" class="text-xs text-gray-400 hover:text-gray-600 py-2">&times; Reset</a>
        <?php endif; ?>
    </form>
    <select onchange="window.location=this.value" class="rounded-xl border border-gray-300 px-3 py-2 text-xs">
        <option value="?"><?php echo e(__('common.all')); ?> <?php echo e(__('common.session')); ?></option>
        <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="?session_id=<?php echo e($s->id); ?>" <?php echo e(request('session_id') == $s->id ? 'selected' : ''); ?>><?php echo e($s->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <form id="bulkForm" method="POST" action="<?php echo e(route('messages.bulk-delete')); ?>" class="hidden">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="ids" id="bulkIds">
    </form>
    <button onclick="bulkDelete()" class="text-xs bg-red-50 text-red-600 px-3 py-2 rounded-xl hover:bg-red-100 transition font-medium">
        <i class="fas fa-trash mr-1"></i> <?php echo e(__('common.delete_selected')); ?>

    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-3 py-2.5 w-8"><input type="checkbox" onchange="toggleAll(this)" class="rounded"></th>
                <th class="px-3 py-2.5"><?php echo e(__('common.receiver')); ?></th>
                <th class="px-3 py-2.5 hidden md:table-cell"><?php echo e(__('common.sender')); ?> (WA)</th>
                <th class="px-3 py-2.5 hidden md:table-cell"><?php echo e(__('common.message')); ?></th>
                <th class="px-3 py-2.5"><?php echo e(__('common.status')); ?></th>
                <th class="px-3 py-2.5"><?php echo e(__('common.session')); ?></th>
                <th class="px-3 py-2.5"><?php echo e(__('common.status')); ?></th>
                <th class="px-3 py-2.5 hidden lg:table-cell"><?php echo e(__('common.time')); ?></th>
                <th class="px-3 py-2.5 w-20 text-right"><?php echo e(__('common.action')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-3 py-2.5"><input type="checkbox" value="<?php echo e($m->id); ?>" class="msg-check rounded"></td>
                <td class="px-3 py-2.5">
                    <div class="font-medium text-gray-900 text-xs"><?php echo e($m->contact?->name ?? preg_replace('/@.*$/', '', $m->phone)); ?></div>
                    <div class="text-[11px] text-gray-400 font-mono"><?php echo e(preg_replace('/@.*$/', '', $m->phone)); ?></div>
                </td>
                <td class="px-3 py-2.5 hidden md:table-cell">
                    <div class="font-medium text-gray-900 text-xs"><?php echo e($m->session?->name ?? '-'); ?></div>
                    <div class="text-[11px] text-gray-400 font-mono"><?php echo e($m->session?->phone ?? '-'); ?></div>
                </td>
                <td class="px-3 py-2.5 hidden md:table-cell text-gray-600 max-w-xs truncate"><?php echo e(\Str::limit($m->message, 60)); ?></td>
                <td class="px-3 py-2.5">
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full
                        <?php echo e($m->status === 'sent' ? 'bg-emerald-50 text-emerald-700' : ''); ?>

                        <?php echo e($m->status === 'failed' ? 'bg-red-50 text-red-700' : ''); ?>

                        <?php echo e($m->status === 'pending' ? 'bg-amber-50 text-amber-700' : ''); ?>">
                        <?php
                        $sentLabels = ['sent'=>'common.sent','failed'=>'common.failed','pending'=>'common.pending','sending'=>'common.sending'];
                        ?>
                        <?php echo e(__($sentLabels[$m->status] ?? $m->status)); ?>

                    </span>
                </td>
                <td class="px-3 py-2.5 hidden lg:table-cell text-xs text-gray-400"><?php echo e($m->created_at->format('d M H:i')); ?></td>
                <td class="px-3 py-2.5 text-right">
                    <?php if($m->status === 'failed'): ?>
                    <form method="POST" action="<?php echo e(route('messages.resend', $m)); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button class="p-1 rounded hover:bg-amber-50 text-gray-400 hover:text-amber-600 text-xs" title="<?php echo e(__('messages.resend')); ?>"><i class="fas fa-redo"></i></button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('messages.destroy', $m)); ?>" class="inline" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="p-1 rounded hover:bg-red-50 text-gray-400 hover:text-red-600 text-xs"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="8" class="px-4 py-16 text-center text-gray-500"><?php echo e(__('messages.empty_sent')); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4"><?php echo e($messages->links()); ?></div>

<script>
function toggleAll(el) {
    document.querySelectorAll('.msg-check').forEach(cb => cb.checked = el.checked);
}
function bulkDelete() {
    const ids = Array.from(document.querySelectorAll('.msg-check:checked')).map(cb => cb.value);
    if (!ids.length) return alert('<?php echo e(__('messages.select_first')); ?>');
    if (!confirm('<?php echo e(__('common.delete')); ?> ' + ids.length + ' <?php echo e(__('common.message')); ?>?')) return;
    document.getElementById('bulkIds').value = JSON.stringify(ids);
    document.getElementById('bulkForm').submit();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\messages\sent.blade.php ENDPATH**/ ?>