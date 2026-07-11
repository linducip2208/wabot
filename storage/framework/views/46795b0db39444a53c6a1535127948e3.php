<?php $__env->startSection('title', $campaign->name . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<a href="<?php echo e(route('campaigns.index')); ?>" class="text-sm text-gray-500 hover:text-brand-600">&larr; <?php echo e(__('common.back')); ?></a>
<h1 class="text-2xl font-extrabold text-gray-900 mt-1 mb-6"><?php echo e($campaign->name); ?></h1>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-3"><?php echo e(__('common.message')); ?></h3>
            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700 whitespace-pre-wrap"><?php echo e($campaign->message); ?></div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-3"><?php echo e(__('common.receiver')); ?> (<?php echo e($campaign->total_recipients); ?>)</h3>
            <div class="max-h-64 overflow-y-auto space-y-1">
                <?php $__currentLoopData = $campaign->recipient_ids; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $c = $contacts[$rid] ?? null ?>
                    <?php if($c): ?>
                    <div class="flex items-center justify-between py-1.5 text-sm">
                        <span class="font-medium text-gray-900"><?php echo e($c->name); ?></span>
                        <span class="text-gray-400 font-mono text-xs"><?php echo e($c->phone); ?></span>
                    </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-4"><?php echo e(__('common.status')); ?></h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500"><?php echo e(__('common.status')); ?></dt>
                    <dd>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            <?php echo e($campaign->status === 'sent' ? 'bg-green-100 text-green-800' : ''); ?>

                            <?php echo e($campaign->status === 'sending' ? 'bg-blue-100 text-blue-800' : ''); ?>

                            <?php echo e($campaign->status === 'draft' ? 'bg-gray-100 text-gray-600' : ''); ?>

                            <?php echo e($campaign->status === 'failed' ? 'bg-red-100 text-red-800' : ''); ?>">
                            <?php echo e($campaign->status); ?>

                        </span>
                    </dd>
                </div>
                <div class="flex justify-between"><dt class="text-gray-500"><?php echo e(__('common.sent')); ?></dt><dd class="font-semibold text-green-600"><?php echo e($campaign->sent_count); ?></dd></div>
                <div class="flex justify-between"><dt class="text-gray-500"><?php echo e(__('common.failed')); ?></dt><dd class="font-semibold text-red-500"><?php echo e($campaign->failed_count); ?></dd></div>
                <div class="flex justify-between"><dt class="text-gray-500"><?php echo e(__('common.total')); ?></dt><dd class="font-semibold text-gray-900"><?php echo e($campaign->total_recipients); ?></dd></div>
                <?php if($campaign->scheduled_at): ?>
                <div class="flex justify-between"><dt class="text-gray-500"><?php echo e(__('campaigns.scheduled_at')); ?></dt><dd class="font-semibold"><?php echo e($campaign->scheduled_at->format('d M Y H:i')); ?></dd></div>
                <?php endif; ?>
                <div class="flex justify-between"><dt class="text-gray-500"><?php echo e(__('common.created')); ?></dt><dd class="font-semibold"><?php echo e($campaign->created_at->format('d M Y H:i')); ?></dd></div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 mb-3"><?php echo e(__('campaigns.progress')); ?></h3>
            <?php $pct = $campaign->total_recipients > 0 ? round(($campaign->sent_count / $campaign->total_recipients) * 100) : 0 ?>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-brand-600 h-2.5 rounded-full" style="width: <?php echo e($pct); ?>%"></div>
            </div>
            <div class="text-xs text-gray-500 mt-2 font-semibold"><?php echo e($pct); ?>% <?php echo e(__('common.completed')); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\campaigns\show.blade.php ENDPATH**/ ?>