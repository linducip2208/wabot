<?php $__env->startSection('title', __('clicktrack.index_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('clicktrack.heading')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('clicktrack.subtitle')); ?></p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="text-3xl font-extrabold text-brand-600"><?php echo e($stats['total_clicks'] ?? 0); ?></div>
        <div class="text-sm text-gray-500 mt-1"><?php echo e(__('clicktrack.total_clicks')); ?></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="text-3xl font-extrabold text-emerald-600"><?php echo e($stats['unique_contacts'] ?? 0); ?></div>
        <div class="text-sm text-gray-500 mt-1"><?php echo e(__('clicktrack.unique_contacts')); ?></div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3"><?php echo e(__('common.contact')); ?></th>
                <th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('clicktrack.link')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('clicktrack.click_time')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 font-medium"><?php echo e($e->contact->name ?? 'N/A'); ?></td>
                <td class="px-5 py-3 text-gray-500 hidden md:table-cell truncate max-w-[300px]"><?php echo e($e->link_url); ?></td>
                <td class="px-5 py-3 text-gray-500"><?php echo e($e->clicked_at->format('d M Y H:i')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="3" class="px-5 py-16 text-center text-gray-500"><?php echo e(__('clicktrack.empty')); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 bg-gray-50 rounded-xl p-4 text-xs text-gray-500">
    <i class="fas fa-info-circle text-brand-500 mr-1"></i> <?php echo e(__('clicktrack.footer_hint')); ?>

</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\click-track\index.blade.php ENDPATH**/ ?>