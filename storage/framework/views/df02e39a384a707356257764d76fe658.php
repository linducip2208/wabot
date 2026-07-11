<?php $__env->startSection('title', 'Transaksi — Admin'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('admin.transaction_mgmt')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('admin.recorded_transactions', ['count' => $transactions->count()])); ?></p>
    </div>
</div>


<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <?php
        $totalAmount = $transactions->where('status', 'completed')->sum('amount');
        $pending = $transactions->where('status', 'pending')->count();
    ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-exchange-alt text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.total')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($transactions->count()); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.completed')); ?></div><div class="text-xl font-extrabold text-gray-900">Rp <?php echo e(number_format($totalAmount, 0, ',', '.')); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-clock text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.pending')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($pending); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center"><i class="fas fa-times-circle text-red-400"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.failed')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($transactions->where('status', 'failed')->count()); ?></div></div>
    </div>
</div>


<div class="flex items-center gap-3 mb-4 flex-wrap">
    <form method="GET" class="flex items-center gap-3 flex-wrap w-full">
        <select name="type" class="rounded-xl border border-gray-300 px-3 py-2 text-xs font-medium text-gray-600 focus:ring-2 focus:ring-brand-500" onchange="this.form.submit()">
            <option value=""><?php echo e(__('common.all')); ?> <?php echo e(__('common.type')); ?></option>
            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($t); ?>" <?php echo e(request('type') === $t ? 'selected' : ''); ?>><?php echo e(ucfirst($t)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <select name="status" class="rounded-xl border border-gray-300 px-3 py-2 text-xs font-medium text-gray-600 focus:ring-2 focus:ring-brand-500" onchange="this.form.submit()">
            <option value=""><?php echo e(__('common.all')); ?> <?php echo e(__('common.status')); ?></option>
            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($s); ?>" <?php echo e(request('status') === $s ? 'selected' : ''); ?>><?php echo e(ucfirst($s)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php if(request('type') || request('status')): ?>
            <a href="<?php echo e(route('admin.transactions.index')); ?>" class="text-xs text-brand-600 hover:underline py-2">Reset Filter</a>
        <?php endif; ?>
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">ID</th>
                <th class="px-5 py-3">User</th>
                <th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('common.plan')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.type')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.amount')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.status')); ?></th>
                <th class="px-5 py-3 hidden lg:table-cell"><?php echo e(__('common.date')); ?></th>
                <th class="px-5 py-3 w-28 text-right"><?php echo e(__('common.action')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3">
                    <span class="font-mono text-xs text-gray-500">#<?php echo e($trx->id); ?></span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[10px] font-bold" style="background: <?php echo e(collect(['#2563eb','#7c3aed','#db2777','#ea580c','#059669','#0891b2'])->get(crc32($trx->user?->name ?? '') % 6)); ?>">
                            <?php echo e(strtoupper(substr($trx->user?->name ?? '?', 0, 2))); ?>

                        </div>
                        <div>
                            <div class="font-medium text-gray-900 text-xs"><?php echo e($trx->user?->name ?? 'N/A'); ?></div>
                            <div class="text-[11px] text-gray-400"><?php echo e($trx->user?->email ?? ''); ?></div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 hidden md:table-cell">
                    <span class="text-xs font-medium text-gray-600"><?php echo e($trx->subscription?->plan?->name ?? '-'); ?></span>
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-700"><?php echo e(ucfirst($trx->type)); ?></span>
                </td>
                <td class="px-5 py-3">
                    <span class="text-sm font-semibold text-gray-900">Rp <?php echo e(number_format($trx->amount, 0, ',', '.')); ?></span>
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                        <?php echo e($trx->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ''); ?>

                        <?php echo e($trx->status === 'pending' ? 'bg-amber-50 text-amber-700' : ''); ?>

                        <?php echo e($trx->status === 'failed' ? 'bg-red-50 text-red-600' : ''); ?>">
                        <?php echo e(ucfirst($trx->status)); ?>

                    </span>
                </td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-400"><?php echo e($trx->created_at->format('d M Y H:i')); ?></td>
                <td class="px-5 py-3 text-right">
                    <?php if($trx->status === 'pending'): ?>
                    <div class="flex items-center justify-end gap-1">
                        <form method="POST" action="<?php echo e(route('admin.transactions.update', $trx)); ?>" class="inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                            <input type="hidden" name="status" value="completed">
                            <button class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600" title="<?php echo e(__('admin.mark_completed')); ?>">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('admin.transactions.update', $trx)); ?>" class="inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                            <input type="hidden" name="status" value="failed">
                            <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600" title="<?php echo e(__('common.mark')); ?> <?php echo e(__('common.failed')); ?>">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <span class="text-xs text-gray-400">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="8" class="px-5 py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-exchange-alt text-xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 font-medium"><?php echo e(__('admin.no_transactions')); ?></p>
                    <p class="text-sm text-gray-400"><?php echo e(__('admin.transactions_empty_hint')); ?></p>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\admin\transactions\index.blade.php ENDPATH**/ ?>