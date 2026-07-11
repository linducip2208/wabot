<?php $__env->startSection('title', 'SLA Logs — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">SLA Logs</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('sla.logs_subtitle')); ?></p>
    </div>
    <a href="<?php echo e(route('sla-configs.index')); ?>" class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2"><i class="fas fa-cog text-xs"></i> Config</a>
</div>

<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex flex-wrap items-end gap-3">
    <div>
        <label class="text-xs font-medium text-gray-500"><?php echo e(__('sla.from_date')); ?></label>
        <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500"><?php echo e(__('sla.to_date')); ?></label>
        <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-xs font-medium text-gray-500"><?php echo e(__('sla.breach_filter')); ?></label>
        <select name="breach_filter" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
            <option value=""><?php echo e(__('common.all')); ?></option>
            <option value="first" <?php echo e(request('breach_filter')=='first' ? 'selected':''); ?>><?php echo e(__('sla.late_response')); ?></option>
            <option value="resolution" <?php echo e(request('breach_filter')=='resolution' ? 'selected':''); ?>><?php echo e(__('sla.late_resolution')); ?></option>
            <option value="any" <?php echo e(request('breach_filter')=='any' ? 'selected':''); ?>><?php echo e(__('sla.any_breach')); ?></option>
        </select>
    </div>
    <button type="submit" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700"><i class="fas fa-filter mr-1"></i> Filter</button>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3"><?php echo e(__('common.contact')); ?></th>
                <th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('sla.agent')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('sla.incoming_message')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('sla.response')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.completed')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.status')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 font-medium text-gray-800"><?php echo e($log->contact?->name ?? '-'); ?></td>
                <td class="px-5 py-3 hidden md:table-cell text-gray-600"><?php echo e($log->teamMember?->name ?? '-'); ?></td>
                <td class="px-5 py-3 text-xs text-gray-500"><?php echo e($log->customer_message_at?->format('d M H:i') ?? '-'); ?></td>
                <td class="px-5 py-3 text-xs <?php echo e($log->first_response_breached ? 'text-red-600 font-medium' : 'text-gray-500'); ?>"><?php echo e($log->first_response_at?->format('d M H:i') ?? '-'); ?></td>
                <td class="px-5 py-3 text-xs <?php echo e($log->resolution_breached ? 'text-red-600 font-medium' : 'text-gray-500'); ?>"><?php echo e($log->resolved_at?->format('d M H:i') ?? '-'); ?></td>
                <td class="px-5 py-3">
                    <?php if($log->first_response_breached || $log->resolution_breached): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-50 text-red-700"><i class="fas fa-exclamation-triangle text-[9px]"></i> Breach</span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700"><i class="fas fa-check text-[9px]"></i> OK</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="px-5 py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><i class="fas fa-history text-gray-400 text-lg"></i></div>
                <p class="text-gray-500 font-medium"><?php echo e(__('sla.no_logs')); ?></p>
            </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4"><?php echo e($logs->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\sla\logs.blade.php ENDPATH**/ ?>