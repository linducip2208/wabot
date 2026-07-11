<?php $__env->startSection('title', __('deals.edit_deal') . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center gap-3 mb-5">
    <a href="<?php echo e(route('deals.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('deals.edit_deal')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e($deal->title); ?></p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
    <form method="POST" action="<?php echo e(route('deals.update', $deal)); ?>" class="space-y-4">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div>
            <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.title')); ?> <?php echo e(__('deals.deal')); ?></label>
            <input type="text" name="title" value="<?php echo e(old('title', $deal->title)); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.contact')); ?></label>
                <select name="contact_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php echo e($deal->contact_id==$c->id ? 'selected':''); ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('deals.stage')); ?></label>
                <select name="stage_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php echo e($deal->stage_id==$s->id ? 'selected':''); ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('deals.value_rp')); ?></label>
                <input type="number" name="value" min="0" step="0.01" value="<?php echo e(old('value', $deal->value)); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('deals.target_close')); ?></label>
                <input type="date" name="expected_close_date" value="<?php echo e(old('expected_close_date', $deal->expected_close_date?->format('Y-m-d'))); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.notes')); ?></label>
            <textarea name="notes" rows="3" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"><?php echo e(old('notes', $deal->notes)); ?></textarea>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="<?php echo e(route('deals.show', $deal)); ?>" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\deals\edit.blade.php ENDPATH**/ ?>