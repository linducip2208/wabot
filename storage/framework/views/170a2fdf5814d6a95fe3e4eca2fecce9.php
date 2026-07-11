<?php $__env->startSection('title', __('catalogs.index_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('catalogs.heading')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('catalogs.subtitle', ['count' => $catalogs->count()])); ?></p>
    </div>
    <a href="<?php echo e(route('catalogs.create')); ?>" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> <?php echo e(__('catalogs.new_catalog')); ?>

    </a>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
    <?php $__empty_1 = true; $__currentLoopData = $catalogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-2">
            <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center"><i class="fas fa-shopping-bag text-orange-500"></i></div>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium <?php echo e($c->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'); ?>"><?php echo e($c->is_active ? __('common.active') : __('common.inactive')); ?></span>
        </div>
        <div class="font-semibold text-gray-900"><?php echo e($c->name); ?></div>
        <p class="text-xs text-gray-400 line-clamp-2 mb-3"><?php echo e($c->description ?: __('catalogs.name_label')); ?></p>
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span><i class="fas fa-box mr-1"></i> <?php echo e($c->items_count); ?> <?php echo e(__('common.items')); ?></span>
            <div class="flex items-center gap-1">
                <a href="<?php echo e(route('catalogs.items', $c)); ?>" class="text-[11px] bg-orange-50 text-orange-700 hover:bg-orange-100 px-2.5 py-1.5 rounded-lg font-medium"><i class="fas fa-boxes"></i> <?php echo e(__('common.items')); ?></a>
                <a href="<?php echo e(route('catalogs.edit', $c)); ?>" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></a>
                <form method="POST" action="<?php echo e(route('catalogs.destroy', $c)); ?>" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> <?php echo e(__('catalogs.heading')); ?>?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-span-full bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-shopping-bag text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1"><?php echo e(__('catalogs.empty_title')); ?></p>
        <p class="text-sm text-gray-400 mb-4"><?php echo e(__('catalogs.empty_subtitle')); ?></p>
        <a href="<?php echo e(route('catalogs.create')); ?>" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> <?php echo e(__('catalogs.empty_cta')); ?></a>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\catalogs\index.blade.php ENDPATH**/ ?>