<?php $__env->startSection('title', __('abtests.create_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center gap-3 mb-5">
    <a href="<?php echo e(route('ab-tests.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('abtests.heading_create')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('abtests.subtitle_create')); ?></p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
    <form method="POST" action="<?php echo e(route('ab-tests.store')); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('abtests.name_label')); ?></label>
                <input type="text" name="name" value="<?php echo e(old('name')); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('catalogs.session_label')); ?></label>
                <select name="session_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value=""><?php echo e(__('common.select')); ?> <?php echo e(__('common.session')); ?>...</option>
                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php echo e(old('session_id')==$s->id ? 'selected':''); ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['session_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="border border-blue-200 rounded-xl p-3 bg-blue-50/30">
                <label class="text-xs font-bold text-blue-600"><?php echo e(__('abtests.variant_a')); ?></label>
                <textarea name="variant_a_message" rows="3" required placeholder="<?php echo e(__('common.message')); ?> varian A" class="w-full mt-1 rounded-xl border border-gray-300 px-3 py-2 text-sm"><?php echo e(old('variant_a_message')); ?></textarea>
                <input type="url" name="media_url_a" value="<?php echo e(old('media_url_a')); ?>" placeholder="Media URL A (opsional)" class="w-full mt-2 rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="border border-purple-200 rounded-xl p-3 bg-purple-50/30">
                <label class="text-xs font-bold text-purple-600"><?php echo e(__('abtests.variant_b')); ?></label>
                <textarea name="variant_b_message" rows="3" required placeholder="<?php echo e(__('common.message')); ?> varian B" class="w-full mt-1 rounded-xl border border-gray-300 px-3 py-2 text-sm"><?php echo e(old('variant_b_message')); ?></textarea>
                <input type="url" name="media_url_b" value="<?php echo e(old('media_url_b')); ?>" placeholder="Media URL B (opsional)" class="w-full mt-2 rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="<?php echo e(route('ab-tests.index')); ?>" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\ab-tests\create.blade.php ENDPATH**/ ?>