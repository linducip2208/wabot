<?php $__env->startSection('title', __('admin.affiliate_withdrawals') . ' — Admin'); ?>
<?php $__env->startSection('content'); ?>

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('admin.affiliate_withdrawals')); ?></h1>
    <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('admin.withdrawals_count', ['count' => $withdrawals->count()])); ?></p>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">ID</th>
                <th class="px-5 py-3"><?php echo e(__('common.user')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.amount')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.method')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.status')); ?></th>
                <th class="px-5 py-3 hidden lg:table-cell"><?php echo e(__('common.date')); ?></th>
                <th class="px-5 py-3 w-32 text-right"><?php echo e(__('common.action')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 text-xs text-gray-400 font-mono">#<?php echo e($w->id); ?></td>
                <td class="px-5 py-3 font-medium text-gray-900"><?php echo e($w->user->name); ?></td>
                <td class="px-5 py-3 font-semibold text-gray-900">Rp <?php echo e(number_format($w->amount, 0, ',', '.')); ?></td>
                <td class="px-5 py-3 text-gray-600 text-xs"><?php echo e($w->payment_method); ?></td>
                <td class="px-5 py-3">
                    <?php if($w->status === 'pending'): ?>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700"><?php echo e(__('common.pending')); ?></span>
                    <?php elseif($w->status === 'approved'): ?>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700"><?php echo e(__('common.approved')); ?></span>
                    <?php else: ?>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-50 text-red-700"><?php echo e(__('common.rejected')); ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell"><?php echo e($w->created_at->format('d M Y H:i')); ?></td>
                <td class="px-5 py-3 text-right">
                    <?php if($w->status === 'pending'): ?>
                    <div class="flex items-center justify-end gap-1">
                        <form method="POST" action="<?php echo e(route('admin.affiliate-withdrawals.approve', $w)); ?>" class="inline">
                            <?php echo csrf_field(); ?>
                            <button class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600" title="<?php echo e(__('common.approve')); ?>">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('admin.affiliate-withdrawals.reject', $w)); ?>" class="inline">
                            <?php echo csrf_field(); ?>
                            <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600" title="<?php echo e(__('common.reject')); ?>">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <span class="text-xs text-gray-400"><?php echo e($w->processed_at?->format('d M Y')); ?></span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="7" class="px-5 py-16 text-center text-gray-500"><?php echo e(__('admin.no_withdrawals')); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\admin\affiliate-withdrawals\index.blade.php ENDPATH**/ ?>