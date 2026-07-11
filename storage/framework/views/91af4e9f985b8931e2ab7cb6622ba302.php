<?php $__env->startSection('title', __('abtests.edit_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center gap-3 mb-5">
    <a href="<?php echo e(route('ab-tests.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('abtests.heading_edit')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e($test->name); ?></p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
    <form method="POST" action="<?php echo e(route('ab-tests.update', $test)); ?>" class="space-y-4">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('abtests.name_label')); ?></label>
                <input type="text" name="name" value="<?php echo e(old('name', $test->name)); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('catalogs.session_label')); ?></label>
                <select name="session_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php echo e($test->session_id==$s->id ? 'selected':''); ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="border border-blue-200 rounded-xl p-3 bg-blue-50/30">
                <label class="text-xs font-bold text-blue-600"><?php echo e(__('abtests.variant_a')); ?></label>
                <textarea name="variant_a_message" rows="3" required class="w-full mt-1 rounded-xl border border-gray-300 px-3 py-2 text-sm"><?php echo e(old('variant_a_message', $test->variant_a_message)); ?></textarea>
                <input type="url" name="media_url_a" value="<?php echo e(old('media_url_a', $test->media_url_a)); ?>" placeholder="Media URL A (opsional)" class="w-full mt-2 rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="border border-purple-200 rounded-xl p-3 bg-purple-50/30">
                <label class="text-xs font-bold text-purple-600"><?php echo e(__('abtests.variant_b')); ?></label>
                <textarea name="variant_b_message" rows="3" required class="w-full mt-1 rounded-xl border border-gray-300 px-3 py-2 text-sm"><?php echo e(old('variant_b_message', $test->variant_b_message)); ?></textarea>
                <input type="url" name="media_url_b" value="<?php echo e(old('media_url_b', $test->media_url_b)); ?>" placeholder="Media URL B (opsional)" class="w-full mt-2 rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="<?php echo e(route('ab-tests.index')); ?>" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\ab-tests\edit.blade.php ENDPATH**/ ?>