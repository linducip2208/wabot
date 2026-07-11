<?php $__env->startSection('title', __('drips.steps_title', ['name' => $dripCampaign->name])); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center gap-3 mb-5">
    <a href="<?php echo e(route('drips.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('drips.steps_title', ['name' => $dripCampaign->name])); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('drips.steps_subtitle', ['count' => $dripCampaign->steps->count()])); ?></p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-3">
        <?php $__empty_1 = true; $__currentLoopData = $dripCampaign->steps->sortBy('step_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <span class="w-8 h-8 rounded-lg bg-teal-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0"><?php echo e($step->step_order); ?></span>
                    <div class="min-w-0">
                        <div class="text-xs text-gray-400 mb-1"><i class="fas fa-hourglass-half mr-1"></i> <?php echo e(__('drips.wait_hours', ['hours' => $step->wait_hours])); ?>

                            <?php if($step->ai_key_id): ?><span class="ml-2 text-violet-600"><i class="fas fa-robot"></i> AI</span><?php endif; ?>
                        </div>
                        <p class="text-sm text-gray-700"><?php echo e($step->message); ?></p>
                        <?php if($step->media_url): ?><a href="<?php echo e($step->media_url); ?>" target="_blank" class="text-xs text-brand-600 hover:underline"><i class="fas fa-paperclip mr-1"></i> <?php echo e(__('common.view')); ?> media</a><?php endif; ?>
                    </div>
                </div>
                <form method="POST" action="<?php echo e(route('drips.steps.destroy', [$dripCampaign, $step])); ?>" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> step?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <i class="fas fa-list-ol text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500"><?php echo e(__('drips.empty_steps')); ?></p>
        </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 h-fit">
        <h2 class="font-bold text-gray-900 mb-3"><?php echo e(__('drips.create_step')); ?></h2>
        <form method="POST" action="<?php echo e(route('drips.steps.store', $dripCampaign)); ?>" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('drips.step_order')); ?></label>
                    <input type="number" name="step_order" min="1" value="<?php echo e(($dripCampaign->steps->max('step_order') ?? 0) + 1); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('drips.wait_hours_label')); ?></label>
                    <input type="number" name="wait_hours" min="0" value="24" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.message')); ?></label>
                <textarea name="message" rows="3" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"></textarea>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('drips.media_url_optional')); ?></label>
                <input type="url" name="media_url" placeholder="https://..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('drips.ai_key_optional')); ?></label>
                <select name="ai_key_id" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                    <option value=""><?php echo e(__('drips.no_ai')); ?></option>
                    <?php $__currentLoopData = $aiKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k->id); ?>"><?php echo e($k->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><i class="fas fa-plus mr-1"></i> <?php echo e(__('drips.create_step')); ?></button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\drips\steps.blade.php ENDPATH**/ ?>