<?php $__env->startSection('title', $deal->title . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('deals.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="text-xl font-extrabold text-gray-900"><?php echo e($deal->title); ?></h1>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('common.created')); ?> <?php echo e($deal->created_at->format('d M Y')); ?></p>
        </div>
    </div>
    <a href="<?php echo e(route('deals.edit', $deal)); ?>" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2"><i class="fas fa-edit text-xs"></i> <?php echo e(__('common.edit')); ?></a>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><div class="text-xs text-gray-500 mb-1"><?php echo e(__('deals.deal_value')); ?></div><div class="text-lg font-extrabold text-emerald-600">Rp <?php echo e(number_format($deal->value, 0, ',', '.')); ?></div></div>
                <div><div class="text-xs text-gray-500 mb-1">Stage</div><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium text-white" style="background: <?php echo e($deal->stage?->color ?? '#6366f1'); ?>"><?php echo e($deal->stage?->name ?? '-'); ?></span></div>
                <div><div class="text-xs text-gray-500 mb-1"><?php echo e(__('common.status')); ?></div><div class="font-medium text-gray-800 capitalize"><?php echo e($deal->status); ?></div></div>
                <div><div class="text-xs text-gray-500 mb-1"><?php echo e(__('deals.target_close')); ?></div><div class="font-medium text-gray-800"><?php echo e($deal->expected_close_date?->format('d M Y') ?? '-'); ?></div></div>
            </div>
        </div>
        <?php if($deal->notes): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-xs font-semibold text-gray-500 mb-2"><?php echo e(__('common.notes')); ?></div>
            <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($deal->notes); ?></p>
        </div>
        <?php endif; ?>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 h-fit">
        <div class="text-xs font-semibold text-gray-500 mb-2"><?php echo e(__('common.contact')); ?></div>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold"><?php echo e(strtoupper(substr($deal->contact?->name ?? 'NA', 0, 2))); ?></div>
            <div>
                <div class="font-medium text-gray-900"><?php echo e($deal->contact?->name ?? '-'); ?></div>
                <div class="text-xs text-gray-400 font-mono"><?php echo e(preg_replace('/@.*$/', '', $deal->contact?->phone ?? '')); ?></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\deals\show.blade.php ENDPATH**/ ?>