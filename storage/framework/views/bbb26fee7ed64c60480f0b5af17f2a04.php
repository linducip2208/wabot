<?php $__env->startSection('title', __('abtests.index_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('abtests.heading')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('abtests.subtitle', ['count' => $tests->count()])); ?></p>
    </div>
    <a href="<?php echo e(route('ab-tests.create')); ?>" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> <?php echo e(__('abtests.new_test')); ?>

    </a>
</div>

<div class="grid gap-3">
    <?php $__empty_1 = true; $__currentLoopData = $tests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
        $aRate = $t->a_sent > 0 ? round($t->a_replied / $t->a_sent * 100, 1) : 0;
        $bRate = $t->b_sent > 0 ? round($t->b_replied / $t->b_sent * 100, 1) : 0;
    ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-fuchsia-50 flex items-center justify-center"><i class="fas fa-flask text-fuchsia-500"></i></div>
                <div>
                    <div class="font-semibold text-gray-900"><?php echo e($t->name); ?></div>
                    <div class="text-xs text-gray-500"><?php echo e($t->session?->name ?? '-'); ?>

                        <?php if($t->winner): ?> · <span class="text-emerald-600 font-medium"><?php echo e(__('abtests.winner')); ?>: <?php echo e($t->winner === 'draw' ? __('abtests.draw') : __('abtests.variant', ['variant' => $t->winner])); ?></span><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium <?php echo e($t->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'); ?>">
                    <?php echo e($t->is_active ? __('common.running') : __('common.stopped')); ?>

                </span>
                <?php if($t->is_active): ?>
                <form method="POST" action="<?php echo e(route('ab-tests.end', $t)); ?>" class="inline"><?php echo csrf_field(); ?><button class="text-[11px] bg-red-50 text-red-700 hover:bg-red-100 px-2.5 py-1.5 rounded-lg font-medium"><?php echo e(__('abtests.end')); ?></button></form>
                <?php else: ?>
                <form method="POST" action="<?php echo e(route('ab-tests.start', $t)); ?>" class="inline"><?php echo csrf_field(); ?><button class="text-[11px] bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-2.5 py-1.5 rounded-lg font-medium"><?php echo e(__('abtests.start')); ?></button></form>
                <?php endif; ?>
                <a href="<?php echo e(route('ab-tests.edit', $t)); ?>" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></a>
                <form method="POST" action="<?php echo e(route('ab-tests.destroy', $t)); ?>" class="inline" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> test?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-lg border <?php echo e($t->winner==='A' ? 'border-emerald-300 bg-emerald-50/50' : 'border-gray-200 bg-gray-50/50'); ?> p-3">
                <div class="flex items-center justify-between mb-1"><span class="text-xs font-bold text-blue-600"><?php echo e(__('abtests.variant_a')); ?></span><span class="text-xs text-gray-500"><?php echo e(__('abtests.reply_rate', ['rate' => $aRate])); ?></span></div>
                <p class="text-xs text-gray-600 line-clamp-2"><?php echo e($t->variant_a_message); ?></p>
                <div class="text-[10px] text-gray-400 mt-1"><?php echo e(__('abtests.replies', ['replied' => $t->a_replied, 'sent' => $t->a_sent])); ?></div>
            </div>
            <div class="rounded-lg border <?php echo e($t->winner==='B' ? 'border-emerald-300 bg-emerald-50/50' : 'border-gray-200 bg-gray-50/50'); ?> p-3">
                <div class="flex items-center justify-between mb-1"><span class="text-xs font-bold text-purple-600"><?php echo e(__('abtests.variant_b')); ?></span><span class="text-xs text-gray-500"><?php echo e(__('abtests.reply_rate', ['rate' => $bRate])); ?></span></div>
                <p class="text-xs text-gray-600 line-clamp-2"><?php echo e($t->variant_b_message); ?></p>
                <div class="text-[10px] text-gray-400 mt-1"><?php echo e(__('abtests.replies', ['replied' => $t->b_replied, 'sent' => $t->b_sent])); ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-flask text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1"><?php echo e(__('abtests.empty_title')); ?></p>
        <p class="text-sm text-gray-400 mb-4"><?php echo e(__('abtests.empty_subtitle')); ?></p>
        <a href="<?php echo e(route('ab-tests.create')); ?>" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> <?php echo e(__('abtests.empty_cta')); ?></a>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\ab-tests\index.blade.php ENDPATH**/ ?>