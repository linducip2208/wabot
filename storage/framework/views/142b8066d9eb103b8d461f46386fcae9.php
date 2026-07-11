<?php $__env->startSection('title', __('buttons.title') . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('buttons.title')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('buttons.subtitle', ['count' => $buttons->count()])); ?></p>
    </div>
    <a href="<?php echo e(route('buttons.create')); ?>" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> <?php echo e(__('buttons.create_button')); ?>

    </a>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
    <?php $__empty_1 = true; $__currentLoopData = $buttons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-lg bg-rose-50 flex items-center justify-center"><i class="fas fa-hand-pointer text-rose-500"></i></div>
                <div class="font-semibold text-gray-900 text-sm"><?php echo e($b->name); ?></div>
            </div>
            <div class="flex items-center gap-1">
                <a href="<?php echo e(route('buttons.edit', $b)); ?>" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></a>
                <form method="POST" action="<?php echo e(route('buttons.destroy', $b)); ?>" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
            </div>
        </div>
        <div class="rounded-lg bg-gray-50 border border-gray-100 p-3 text-sm">
            <?php if($b->header_text): ?><div class="font-semibold text-gray-800 mb-1"><?php echo e($b->header_text); ?></div><?php endif; ?>
            <p class="text-gray-600 text-xs mb-2 line-clamp-3"><?php echo e($b->body_text); ?></p>
            <?php if($b->footer_text): ?><div class="text-[10px] text-gray-400 mb-2"><?php echo e($b->footer_text); ?></div><?php endif; ?>
            <div class="space-y-1">
                <?php $__currentLoopData = ($b->buttons ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $btn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="w-full text-center text-xs text-brand-600 border border-brand-200 rounded-md py-1 bg-white"><?php echo e(is_array($btn) ? ($btn['text'] ?? $btn['title'] ?? __('buttons.fallback_button')) : $btn); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <div class="text-[10px] text-gray-400 mt-2"><?php echo e($b->session?->name ?? __('common.all') . ' ' . __('common.session')); ?></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-span-full bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-hand-pointer text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1"><?php echo e(__('buttons.empty_title')); ?></p>
        <p class="text-sm text-gray-400 mb-4"><?php echo e(__('buttons.empty_desc')); ?></p>
        <a href="<?php echo e(route('buttons.create')); ?>" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> <?php echo e(__('buttons.create_button')); ?></a>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\buttons\index.blade.php ENDPATH**/ ?>