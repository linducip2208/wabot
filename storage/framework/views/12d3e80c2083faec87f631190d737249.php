<?php $__env->startSection('title', 'Deal Stages — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center gap-3 mb-5">
    <a href="<?php echo e(route('deals.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Deal Stages</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e($stages->count()); ?> <?php echo e(__('deals.stages_subtitle')); ?></p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-2">
        <?php $__empty_1 = true; $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="POST" action="<?php echo e(route('deal-stages.update', $stage)); ?>" class="flex items-center gap-3">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <input type="color" name="color" value="<?php echo e($stage->color ?? '#6366f1'); ?>" class="w-9 h-9 rounded-lg border border-gray-200 cursor-pointer">
                <input type="text" name="name" value="<?php echo e($stage->name); ?>" required class="flex-1 rounded-xl border border-gray-300 px-3 py-2 text-sm">
                <input type="number" name="sort_order" value="<?php echo e($stage->sort_order); ?>" class="w-16 rounded-xl border border-gray-300 px-2 py-2 text-sm text-center">
                <span class="text-xs text-gray-400 whitespace-nowrap"><?php echo e($stage->deals_count); ?> deal</span>
                <button type="submit" class="p-2 rounded-lg bg-brand-50 text-brand-600 hover:bg-brand-100"><i class="fas fa-save text-xs"></i></button>
            </form>
            <form method="POST" action="<?php echo e(route('deal-stages.destroy', $stage)); ?>" class="mt-2 flex justify-end" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> stage?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="text-xs text-red-500 hover:text-red-700"><i class="fas fa-trash mr-1"></i> <?php echo e(__('common.delete')); ?></button>
            </form>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <i class="fas fa-layer-group text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500"><?php echo e(__('deals.no_stages_hint')); ?></p>
        </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 h-fit">
        <h2 class="font-bold text-gray-900 mb-3"><?php echo e(__('common.create')); ?> Stage</h2>
        <form method="POST" action="<?php echo e(route('deal-stages.store')); ?>" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?> Stage</label>
                <input type="text" name="name" required placeholder="Prospek" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.color')); ?></label>
                    <input type="color" name="color" value="#6366f1" class="w-full h-10 rounded-xl border border-gray-300 cursor-pointer">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.order')); ?></label>
                    <input type="number" name="sort_order" value="<?php echo e(($stages->max('sort_order') ?? 0) + 1); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><i class="fas fa-plus mr-1"></i> <?php echo e(__('common.create')); ?> Stage</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\deals\stages.blade.php ENDPATH**/ ?>