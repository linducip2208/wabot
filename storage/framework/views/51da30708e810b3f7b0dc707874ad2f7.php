<?php $__env->startSection('title', 'Conversation Ratings — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Conversation Ratings</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('ratings.ratings_total', ['total' => $totalRatings])); ?></p>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 card-lift">
        <div class="w-14 h-14 rounded-xl bg-amber-50 flex items-center justify-center"><i class="fas fa-star text-amber-500 text-2xl"></i></div>
        <div>
            <div class="text-3xl font-extrabold text-gray-900"><?php echo e($average); ?></div>
            <div class="text-xs text-gray-500"><?php echo e(__('ratings.average_out_of_5')); ?></div>
        </div>
    </div>
    <div class="md:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <div class="space-y-1.5">
            <?php for($i = 5; $i >= 1; $i--): ?>
            <?php $count = $distributionData[$i] ?? 0; $pct = $totalRatings > 0 ? round($count / $totalRatings * 100) : 0; ?>
            <div class="flex items-center gap-2 text-xs">
                <span class="w-10 text-gray-500"><?php echo e($i); ?> <i class="fas fa-star text-amber-400 text-[9px]"></i></span>
                <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full bg-amber-400" style="width: <?php echo e($pct); ?>%"></div></div>
                <span class="w-10 text-right text-gray-600 font-medium"><?php echo e($count); ?></span>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex flex-wrap items-end gap-3">
    <div>
        <label class="text-xs font-medium text-gray-500">Rating</label>
        <select name="rating" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
            <option value=""><?php echo e(__('common.all')); ?></option>
            <?php for($i=5;$i>=1;$i--): ?><option value="<?php echo e($i); ?>" <?php echo e(request('rating')==$i ? 'selected':''); ?>><?php echo e($i); ?> <?php echo e(__('ratings.stars')); ?></option><?php endfor; ?>
        </select>
    </div>
            <div><label class="text-xs font-medium text-gray-500"><?php echo e(__('ratings.from')); ?></label><input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="rounded-xl border border-gray-300 px-3 py-2 text-sm"></div>
            <div><label class="text-xs font-medium text-gray-500"><?php echo e(__('ratings.to')); ?></label><input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="rounded-xl border border-gray-300 px-3 py-2 text-sm"></div>
            <button type="submit" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700"><i class="fas fa-filter mr-1"></i> <?php echo e(__('common.filter')); ?></button>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider"><th class="px-5 py-3"><?php echo e(__('common.contact')); ?></th><th class="px-5 py-3"><?php echo e(__('ratings.rating')); ?></th><th class="px-5 py-3"><?php echo e(__('ratings.comment')); ?></th><th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('common.date')); ?></th><th class="px-5 py-3 w-16 text-right"><?php echo e(__('common.action')); ?></th></tr></thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $ratings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 font-medium text-gray-800"><?php echo e($r->contact?->name ?? '-'); ?></td>
                <td class="px-5 py-3">
                    <?php for($i=1;$i<=5;$i++): ?><i class="fas fa-star text-xs <?php echo e($i <= $r->rating ? 'text-amber-400' : 'text-gray-200'); ?>"></i><?php endfor; ?>
                </td>
                <td class="px-5 py-3 text-gray-600 max-w-xs truncate"><?php echo e($r->comment ?: '-'); ?></td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-400"><?php echo e($r->created_at->format('d M Y H:i')); ?></td>
                <td class="px-5 py-3 text-right"><a href="<?php echo e(route('ratings.show', $r)); ?>" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-eye text-xs"></i></a></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="px-5 py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><i class="fas fa-star text-gray-400 text-lg"></i></div>
                <p class="text-gray-500 font-medium"><?php echo e(__('ratings.no_ratings')); ?></p>
            </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4"><?php echo e($ratings->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\ratings\index.blade.php ENDPATH**/ ?>