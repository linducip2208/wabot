<?php $__env->startSection('title', 'Deals — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Deals</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('deals.subtitle', ['count' => $deals->count(), 'value' => number_format($deals->sum('value'), 0, ',', '.')])); ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('deal-stages.index')); ?>" class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2"><i class="fas fa-layer-group text-xs"></i> Stages</a>
        <a href="<?php echo e(route('deals.board')); ?>" class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2"><i class="fas fa-columns text-xs"></i> Kanban</a>
        <a href="<?php echo e(route('deals.create')); ?>" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2"><i class="fas fa-plus text-xs"></i> <?php echo e(__('deals.new_deal')); ?></a>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">Deal</th>
                <th class="px-5 py-3"><?php echo e(__('common.contact')); ?></th>
                <th class="px-5 py-3">Stage</th>
                <th class="px-5 py-3"><?php echo e(__('deals.value')); ?></th>
                <th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('deals.target_close')); ?></th>
                <th class="px-5 py-3 w-24 text-right"><?php echo e(__('common.action')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $deals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3"><a href="<?php echo e(route('deals.show', $d)); ?>" class="font-medium text-gray-900 hover:text-brand-600"><?php echo e($d->title); ?></a></td>
                <td class="px-5 py-3 text-gray-600"><?php echo e($d->contact?->name ?? '-'); ?></td>
                <td class="px-5 py-3"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium text-white" style="background: <?php echo e($d->stage?->color ?? '#6366f1'); ?>"><?php echo e($d->stage?->name ?? '-'); ?></span></td>
                <td class="px-5 py-3 font-semibold text-gray-800">Rp <?php echo e(number_format($d->value, 0, ',', '.')); ?></td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-400"><?php echo e($d->expected_close_date?->format('d M Y') ?? '-'); ?></td>
                <td class="px-5 py-3 text-right">
                    <a href="<?php echo e(route('deals.edit', $d)); ?>" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></a>
                    <form method="POST" action="<?php echo e(route('deals.destroy', $d)); ?>" class="inline" onsubmit="return confirm('<?php echo e(__('deals.delete_confirm')); ?>')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="px-5 py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><i class="fas fa-handshake text-gray-400 text-lg"></i></div>
                <p class="text-gray-500 font-medium"><?php echo e(__('deals.no_deals')); ?></p>
                <p class="text-sm text-gray-400 mt-1"><?php echo e(__('deals.no_deals_hint')); ?></p>
            </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\deals\index.blade.php ENDPATH**/ ?>