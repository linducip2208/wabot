<?php $__env->startSection('title', __('credits.page_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('credits.page_title')); ?></h1>
    <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('credits.subtitle')); ?></p>
</div>


<div class="grid lg:grid-cols-3 gap-5 mb-5">
    <div class="bg-gradient-to-br from-brand-600 to-brand-800 rounded-2xl p-6 text-white shadow-lg lg:col-span-1">
        <div class="text-sm text-brand-200 mb-1"><?php echo e(__('credits.current_balance')); ?></div>
        <div class="text-4xl font-extrabold tracking-tight"><?php echo e(number_format($balance)); ?></div>
        <div class="text-brand-300 text-sm mt-1"><?php echo e(__('credits.credits')); ?></div>
        <div class="mt-4 flex items-center gap-2 text-xs text-brand-200">
            <i class="fas fa-info-circle"></i> <?php echo e(__('credits.one_credit_per_ai_call')); ?>

        </div>
    </div>

    <div class="lg:col-span-2">
        <h2 class="text-sm font-semibold text-gray-700 mb-3"><?php echo e(__('credits.buy_credits')); ?></h2>
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
            <?php $__empty_1 = true; $__currentLoopData = $packs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pack): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex flex-col">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1"><?php echo e($pack->name); ?></div>
                <div class="text-2xl font-extrabold text-gray-900"><?php echo e(number_format($pack->credits)); ?> <span class="text-sm font-medium text-gray-400"><?php echo e(__('credits.credits')); ?></span></div>
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-lg font-bold text-brand-600">Rp <?php echo e(number_format($pack->price, 0, ',', '.')); ?></span>
                    <form method="POST" action="<?php echo e(route('credits.purchase')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="pack_id" value="<?php echo e($pack->id); ?>">
                        <button class="bg-brand-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-brand-700 transition">
                            <?php echo e(__('credits.buy')); ?>

                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-full bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-coins text-xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 font-medium"><?php echo e(__('credits.no_packs')); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700"><?php echo e(__('credits.transaction_history')); ?></h2>
        <span class="text-xs text-gray-400"><?php echo e(trans_choice('credits.count', $transactions->count())); ?></span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3"><?php echo e(__('credits.type')); ?></th>
                    <th class="px-5 py-3"><?php echo e(__('credits.amount')); ?></th>
                    <th class="px-5 py-3"><?php echo e(__('credits.balance')); ?></th>
                    <th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('credits.description')); ?></th>
                    <th class="px-5 py-3 hidden lg:table-cell"><?php echo e(__('credits.date')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50/50">
                    <td class="px-5 py-3">
                        <?php if($txn->type === 'purchase'): ?>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700"><?php echo e(__('credits.type_purchase')); ?></span>
                        <?php elseif($txn->type === 'usage'): ?>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-50 text-red-700"><?php echo e(__('credits.type_usage')); ?></span>
                        <?php elseif($txn->type === 'refund'): ?>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700"><?php echo e(__('credits.type_refund')); ?></span>
                        <?php else: ?>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-blue-50 text-blue-700"><?php echo e(__('credits.type_grant')); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3">
                        <span class="font-semibold <?php echo e($txn->amount >= 0 ? 'text-emerald-600' : 'text-red-600'); ?>">
                            <?php echo e($txn->amount >= 0 ? '+' : ''); ?><?php echo e(number_format($txn->amount)); ?>

                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-600"><?php echo e(number_format($txn->balance_after)); ?></td>
                    <td class="px-5 py-3 text-gray-500 hidden md:table-cell"><?php echo e($txn->description ?: '-'); ?></td>
                    <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell"><?php echo e($txn->created_at->format('d M Y H:i')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5" class="px-5 py-16 text-center text-gray-500"><?php echo e(__('credits.no_transactions')); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\credits\index.blade.php ENDPATH**/ ?>