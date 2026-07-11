<?php $__env->startSection('title', 'Log Aktivitas — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('logger.page_title')); ?></h1>
    <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('logger.subtitle')); ?></p>
</div>


<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-list-alt text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.total')); ?> Log</div><div class="text-xl font-extrabold text-gray-900"><?php echo e($logs->count()); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-mobile-alt text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.session')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($logs->where('type','session')->count()); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center"><i class="fas fa-plug text-violet-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Webhook</div><div class="text-xl font-extrabold text-gray-900"><?php echo e($logs->where('type','webhook')->count()); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-clock text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('sentiment.today')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($logs->where(fn($l) => \Carbon\Carbon::parse($l['created_at'])->isToday())->count()); ?></div></div>
    </div>
</div>


<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3"><?php echo e(__('common.type')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('logger.event')); ?></th>
                <th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('common.detail')); ?></th>
                <th class="px-5 py-3 hidden lg:table-cell"><?php echo e(__('common.time')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3">
                    <?php if($log['type'] === 'session'): ?>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700"><i class="fas fa-mobile-alt mr-1"></i><?php echo e(__('common.session')); ?></span>
                    <?php else: ?>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-violet-50 text-violet-700"><i class="fas fa-plug mr-1"></i>Webhook</span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3">
                    <span class="font-medium text-gray-900 text-xs"><?php echo e($log['event']); ?></span>
                </td>
                <td class="px-5 py-3 text-gray-600 text-xs hidden md:table-cell max-w-[320px] truncate"><?php echo e($log['detail']); ?></td>
                <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                    <?php echo e(\Carbon\Carbon::parse($log['created_at'])->translatedFormat('d M Y H:i')); ?>

                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="4" class="px-5 py-16 text-center text-gray-500"><?php echo e(__('logger.no_logs')); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if($logs->count() > 50): ?>
<div class="text-center mt-3 text-xs text-gray-400"><?php echo e(__('logger.showing_recent', ['count' => $logs->count()])); ?></div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\logger\index.blade.php ENDPATH**/ ?>