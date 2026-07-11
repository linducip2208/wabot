<?php $__env->startSection('title', __('messages.received_title') . ' — ' . config('app.name')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('messages.received_title')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e($messages->total()); ?> <?php echo e(__('common.message')); ?> <?php echo e(__('messages.received_count')); ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('messages.received')); ?>" class="bg-sky-600 text-white px-3 py-2 rounded-xl text-sm font-medium">
            <i class="fas fa-inbox mr-1"></i> <?php echo e(__('messages.inbox')); ?>

        </a>
        <a href="<?php echo e(route('messages.sent')); ?>" class="bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
            <i class="fas fa-paper-plane mr-1"></i> <?php echo e(__('common.sent')); ?>

        </a>
        <a href="<?php echo e(route('messages.queue')); ?>" class="bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
            <i class="fas fa-clock mr-1"></i> <?php echo e(__('messages.queue')); ?>

        </a>
    </div>
</div>

    <form id="bulkForm" method="POST" action="<?php echo e(route('messages.bulk-delete')); ?>" class="hidden">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="ids" id="bulkIds">
    </form>

    <div class="flex gap-2 mb-4 items-center">
        <form method="GET" action="<?php echo e(route('messages.search')); ?>" class="flex-1 flex gap-2">
            <input type="hidden" name="direction" value="in">
            <input type="text" name="q" placeholder="Cari pesan, nomor, atau nama kontak..." value="<?php echo e(request('q')); ?>"
                class="flex-1 rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            <button type="submit" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-brand-700">
                <i class="fas fa-search mr-1"></i> Cari
            </button>
            <?php if(request('q')): ?>
            <a href="<?php echo e(route('messages.received')); ?>" class="text-xs text-gray-400 hover:text-gray-600 py-2 px-1">&times; Reset</a>
            <?php endif; ?>
        </form>
    </div>
<div class="mb-4">
    <button onclick="bulkDelete()" class="text-xs bg-red-50 text-red-600 px-3 py-2 rounded-xl hover:bg-red-100 transition font-medium">
        <i class="fas fa-trash mr-1"></i> <?php echo e(__('common.delete_selected')); ?>

    </button>
</div>

<div class="space-y-2">
    <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm transition">
        <div class="flex items-start gap-3">
            <input type="checkbox" value="<?php echo e($m->id); ?>" class="msg-check rounded mt-1">
            <div class="w-8 h-8 rounded-full bg-sky-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user text-sky-500 text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-sm text-gray-900"><?php echo e($m->contact?->name ?? preg_replace('/@.*$/', '', $m->phone)); ?></span>
                    <span class="text-[11px] text-gray-400 font-mono"><?php echo e(preg_replace('/@.*$/', '', $m->phone)); ?></span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-sky-50 text-sky-700 font-medium ml-auto"><?php echo e(__('messages.inbox')); ?></span>
                </div>
                <p class="text-sm text-gray-700 mb-1 cursor-pointer hover:text-brand-600 line-clamp-1"
                    onclick="this.classList.toggle('line-clamp-none'); this.classList.toggle('line-clamp-1')"
                    title="<?php echo e(__('messages.click_to_view_all')); ?>"><?php echo e($m->message); ?></p>
                <div class="flex items-center gap-3 text-[11px] text-gray-400 flex-wrap">
                    <span class="font-mono text-gray-500"><?php echo e($m->contact?->phone ?? preg_replace('/@.*$/', '', $m->phone)); ?></span>
                    <i class="fas fa-arrow-right text-[8px]"></i>
                    <span class="font-mono text-gray-500"><?php echo e($m->session?->phone ?? '-'); ?></span>
                    <span class="text-gray-400">(<?php echo e($m->session?->name ?? '-'); ?>)</span>
                    <span class="ml-auto"><?php echo e($m->created_at->format('d M Y H:i')); ?></span>
                    <a href="<?php echo e(route('chat.conversation', $m->contact_id)); ?>" class="text-brand-600 hover:underline">
                        <i class="fas fa-reply mr-1"></i> <?php echo e(__('messages.reply')); ?>

                    </a>
                    <form method="POST" action="<?php echo e(route('messages.destroy', $m)); ?>" class="inline" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="text-gray-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center text-gray-500"><?php echo e(__('messages.empty_received')); ?></div>
    <?php endif; ?>
</div>

<div class="mt-4"><?php echo e($messages->links()); ?></div>

<script>
function toggleAll(el) { document.querySelectorAll('.msg-check').forEach(cb => cb.checked = el.checked); }
function bulkDelete() {
    const ids = Array.from(document.querySelectorAll('.msg-check:checked')).map(cb => cb.value);
    if (!ids.length) return alert('<?php echo e(__('messages.select_first')); ?>');
    if (!confirm('<?php echo e(__('common.delete')); ?> ' + ids.length + ' <?php echo e(__('common.message')); ?>?')) return;
    document.getElementById('bulkIds').value = JSON.stringify(ids);
    document.getElementById('bulkForm').submit();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\messages\received.blade.php ENDPATH**/ ?>