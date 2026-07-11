<?php $__env->startSection('title', 'Call Logs: ' . $broadcast->name . ' — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('calls.index')); ?>" class="text-gray-400 hover:text-brand-600 transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-extrabold text-gray-900">Call Logs: <?php echo e($broadcast->name); ?></h1>
            </div>
            <p class="text-sm text-gray-500 mt-0.5">
                <span class="text-blue-600"><?php echo e($broadcast->called_count); ?>/<?php echo e($broadcast->total_recipients); ?></span> <?php echo e(__('common.sent')); ?> &middot;
                <span class="text-green-600"><?php echo e($broadcast->answered_count); ?></span> <?php echo e(__('calls.answered')); ?> &middot;
                <span class="text-red-500"><?php echo e($broadcast->failed_count); ?></span> <?php echo e(__('common.failed')); ?>

            </p>
        </div>
    </div>

    <?php if($logs->isEmpty()): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-phone-slash text-gray-300 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1"><?php echo e(__('calls.no_logs')); ?></h3>
            <p class="text-sm text-gray-400"><?php echo e(__('calls.no_logs_hint')); ?></p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3">Phone</th>
                            <th class="px-5 py-3"><?php echo e(__('common.contact')); ?></th>
                            <th class="px-5 py-3"><?php echo e(__('common.status')); ?></th>
                            <th class="px-5 py-3"><?php echo e(__('common.duration')); ?></th>
                            <th class="px-5 py-3"><?php echo e(__('calls.audio')); ?></th>
                            <th class="px-5 py-3"><?php echo e(__('common.notes')); ?></th>
                            <th class="px-5 py-3"><?php echo e(__('common.time')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-3 font-mono text-xs"><?php echo e($log->phone); ?></td>
                                <td class="px-5 py-3"><?php echo e($log->contact?->name ?? '-'); ?></td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php echo e($log->status === 'confirmed' ? 'bg-green-100 text-green-800' : ''); ?>

                                        <?php echo e($log->status === 'sent' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                        <?php echo e($log->status === 'failed' ? 'bg-red-100 text-red-800' : ''); ?>

                                        <?php echo e($log->status === 'pending' ? 'bg-gray-100 text-gray-600' : ''); ?>">
                                        <?php echo e(ucfirst($log->status)); ?>

                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-500"><?php echo e($log->duration_seconds ? $log->duration_seconds . 's' : '-'); ?></td>
                                <td class="px-5 py-3">
                                    <?php if($log->audio_url): ?>
                                        <audio controls class="h-7 w-40"><source src="<?php echo e($log->audio_url); ?>"></audio>
                                    <?php else: ?> - <?php endif; ?>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-400 max-w-[150px] truncate"><?php echo e($log->notes ?? '-'); ?></td>
                                <td class="px-5 py-3 text-xs text-gray-400"><?php echo e($log->created_at->format('d/m H:i')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4"><?php echo e($logs->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\calls\logs.blade.php ENDPATH**/ ?>