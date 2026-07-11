<?php $__env->startSection('title', __('deals.new_deal') . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center gap-3 mb-5">
    <a href="<?php echo e(route('deals.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('deals.new_deal')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('common.create')); ?> <?php echo e(__('deals.deal')); ?></p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
    <form method="POST" action="<?php echo e(route('deals.store')); ?>" class="space-y-4">
        <?php echo csrf_field(); ?>
        <div>
            <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.title')); ?> <?php echo e(__('deals.deal')); ?></label>
            <input type="text" name="title" value="<?php echo e(old('title')); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.contact')); ?></label>
                <select name="contact_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value=""><?php echo e(__('common.select')); ?> <?php echo e(__('common.contact')); ?>...</option>
                    <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php echo e(old('contact_id')==$c->id ? 'selected':''); ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('deals.stage')); ?></label>
                <select name="stage_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value=""><?php echo e(__('common.select')); ?> <?php echo e(__('deals.stage')); ?>...</option>
                    <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php echo e(old('stage_id')==$s->id ? 'selected':''); ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('deals.value_rp')); ?></label>
                <input type="number" name="value" min="0" step="0.01" value="<?php echo e(old('value', 0)); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('deals.target_close')); ?></label>
                <input type="date" name="expected_close_date" value="<?php echo e(old('expected_close_date')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.notes')); ?></label>
            <textarea name="notes" rows="3" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"><?php echo e(old('notes')); ?></textarea>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="<?php echo e(route('deals.index')); ?>" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\deals\create.blade.php ENDPATH**/ ?>