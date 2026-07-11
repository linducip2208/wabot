<?php $__env->startSection('title', __('common.detail') . ' Rating — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center gap-3 mb-5">
    <a href="<?php echo e(route('ratings.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('common.detail')); ?> Rating</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e($rating->created_at->format('d M Y H:i')); ?></p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center">
            <div class="mb-2">
                <?php for($i=1;$i<=5;$i++): ?><i class="fas fa-star text-2xl <?php echo e($i <= $rating->rating ? 'text-amber-400' : 'text-gray-200'); ?>"></i><?php endfor; ?>
            </div>
            <div class="text-3xl font-extrabold text-gray-900"><?php echo e($rating->rating); ?><span class="text-lg text-gray-400">/5</span></div>
        </div>
        <?php if($rating->comment): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-xs font-semibold text-gray-500 mb-2"><?php echo e(__('ratings.comment_from')); ?></div>
            <p class="text-sm text-gray-700 whitespace-pre-line"><?php echo e($rating->comment); ?></p>
        </div>
        <?php endif; ?>
        <?php if($rating->message): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-xs font-semibold text-gray-500 mb-2"><?php echo e(__('ratings.related_message')); ?></div>
            <p class="text-sm text-gray-700"><?php echo e($rating->message->message ?? '-'); ?></p>
        </div>
        <?php endif; ?>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 h-fit">
        <div class="text-xs font-semibold text-gray-500 mb-2"><?php echo e(__('common.contact')); ?></div>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold"><?php echo e(strtoupper(substr($rating->contact?->name ?? 'NA', 0, 2))); ?></div>
            <div>
                <div class="font-medium text-gray-900"><?php echo e($rating->contact?->name ?? '-'); ?></div>
                <div class="text-xs text-gray-400 font-mono"><?php echo e(preg_replace('/@.*$/', '', $rating->contact?->phone ?? '')); ?></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\ratings\show.blade.php ENDPATH**/ ?>