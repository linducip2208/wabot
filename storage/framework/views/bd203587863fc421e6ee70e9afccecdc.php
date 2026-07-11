<?php $__env->startSection('title', 'Submissions: ' . $form->name . ' — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('forms.index')); ?>" class="text-gray-400 hover:text-brand-600 transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-2xl font-extrabold text-gray-900">Submissions: <?php echo e($form->name); ?></h1>
            </div>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('common.total')); ?>: <?php echo e($submissions->total()); ?></p>
        </div>
        <a href="<?php echo e(route('forms.export', $form)); ?>"
            class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
    </div>

    <?php if($submissions->isEmpty()): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-envelope-open-text text-gray-300 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1"><?php echo e(__('submissions.empty')); ?></h3>
            <p class="text-sm text-gray-400"><?php echo e(__('submissions.empty_hint')); ?></p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3">#</th>
                            <th class="px-5 py-3">Phone</th>
                            <th class="px-5 py-3"><?php echo e(__('common.contact')); ?></th>
                            <?php $__currentLoopData = $form->components ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-5 py-3"><?php echo e($comp['label'] ?? ''); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <th class="px-5 py-3"><?php echo e(__('common.date')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $submissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-5 py-3 text-gray-500"><?php echo e($submissions->firstItem() + $i); ?></td>
                                <td class="px-5 py-3 font-mono text-xs"><?php echo e($sub->phone); ?></td>
                                <td class="px-5 py-3"><?php echo e($sub->contact?->name ?? '-'); ?></td>
                                <?php $__currentLoopData = $form->components ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $key = $comp['label'] ?? ''; ?>
                                    <td class="px-5 py-3"><?php echo e($sub->data[$key] ?? '-'); ?></td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <td class="px-5 py-3 text-gray-400 text-xs"><?php echo e($sub->created_at->format('d/m/Y H:i')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4"><?php echo e($submissions->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\forms\submissions.blade.php ENDPATH**/ ?>