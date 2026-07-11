<?php $__env->startSection('title', __('drips.edit_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center gap-3 mb-5">
    <a href="<?php echo e(route('drips.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('drips.heading_edit')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e($dripCampaign->name); ?></p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
    <form method="POST" action="<?php echo e(route('drips.update', $dripCampaign)); ?>" class="space-y-4">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div>
            <label class="text-xs font-medium text-gray-500"><?php echo e(__('drips.name_label')); ?></label>
            <input type="text" name="name" value="<?php echo e(old('name', $dripCampaign->name)); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
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
                <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($s->id); ?>" <?php echo e($dripCampaign->session_id==$s->id ? 'selected' : ''); ?>><?php echo e($s->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.status')); ?></label>
                <select name="is_active" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="1" <?php echo e($dripCampaign->is_active ? 'selected' : ''); ?>><?php echo e(__('common.active')); ?></option>
                    <option value="0" <?php echo e(!$dripCampaign->is_active ? 'selected' : ''); ?>><?php echo e(__('common.inactive')); ?></option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('drips.target')); ?></label>
                <select name="send_to_new_only" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="1" <?php echo e($dripCampaign->send_to_new_only ? 'selected' : ''); ?>><?php echo e(__('common.contact')); ?> <?php echo e(__('common.new')); ?></option>
                    <option value="0" <?php echo e(!$dripCampaign->send_to_new_only ? 'selected' : ''); ?>><?php echo e(__('common.all')); ?> <?php echo e(__('common.contact')); ?></option>
                </select>
            </div>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="<?php echo e(route('drips.steps', $dripCampaign)); ?>" class="flex-1 text-center bg-teal-50 text-teal-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('drips.manage_steps')); ?></a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\drips\edit.blade.php ENDPATH**/ ?>