<?php $__env->startSection('title', 'Langganan Saya — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('subscriptions.my_subscriptions')); ?></h1>
    <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('subscriptions.subtitle')); ?></p>
</div>


<?php if($currentPlan): ?>
<div class="mb-6 bg-gradient-to-r from-brand-50 to-blue-50 border border-brand-200 rounded-2xl px-5 py-4">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center"><i class="fas fa-crown text-brand-600"></i></div>
            <div>
                <span class="text-sm text-brand-800"><?php echo e(__('common.plan')); ?> <?php echo e(__('common.active')); ?>: <strong class="text-base"><?php echo e($currentPlan->name); ?></strong></span>
                <?php if($currentSubscription): ?>
                <div class="text-xs text-brand-600 mt-0.5">
                    <?php if($currentSubscription->ends_at): ?>
                        <span><i class="fas fa-calendar-alt mr-1"></i> <?php echo e(__('subscriptions.valid_until')); ?> <?php echo e($currentSubscription->ends_at->format('d M Y')); ?></span>
                    <?php else: ?>
                        <span><i class="fas fa-calendar-alt mr-1"></i> <?php echo e(__('subscriptions.active_forever')); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <span class="text-xs bg-brand-100 text-brand-700 px-3 py-1.5 rounded-full font-medium"><?php echo e(__('common.active')); ?></span>
    </div>
</div>
<?php else: ?>
<div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 flex items-center gap-3">
    <i class="fas fa-exclamation-triangle text-amber-500"></i>
    <div>
        <span class="text-sm text-amber-800 font-medium"><?php echo e(__('subscriptions.no_active_plan')); ?></span>
        <a href="<?php echo e(route('plans.index')); ?>" class="text-sm text-brand-600 font-semibold hover:underline ml-1"><?php echo e(__('subscriptions.select_plan_now')); ?></a>
    </div>
</div>
<?php endif; ?>


<div class="grid md:grid-cols-3 gap-3 mb-6">
    <?php $__currentLoopData = [
        ['fas fa-mobile-alt', __('common.session') . ' WhatsApp', $usage['sessions']['current'], $usage['sessions']['limit'], 'bg-sky-50 text-sky-500'],
        ['fas fa-address-book', __('common.contact'), $usage['contacts']['current'], $usage['contacts']['limit'], 'bg-violet-50 text-violet-500'],
        ['fas fa-robot', 'Auto-Reply', $usage['autoreplies']['current'], $usage['autoreplies']['limit'], 'bg-emerald-50 text-emerald-500'],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$icon, $label, $current, $limit, $colorClass]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg <?php echo e($colorClass); ?> flex items-center justify-center"><i class="<?php echo e($icon); ?> text-sm"></i></div>
                <span class="text-sm font-medium text-gray-700"><?php echo e($label); ?></span>
            </div>
            <span class="text-xs font-mono <?php echo e($limit > 0 && $current >= $limit ? 'text-red-500' : 'text-gray-400'); ?>">
                <?php echo e(number_format($current)); ?> / <?php echo e($limit > 0 ? number_format($limit) : '∞'); ?>

            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="h-2 rounded-full transition-all
                <?php echo e($limit > 0 ? ($current >= $limit ? 'bg-red-400' : 'bg-emerald-400') : 'bg-brand-400'); ?>"
                style="width: <?php echo e($limit > 0 ? min(($current / max($limit, 1)) * 100, 100) : 100); ?>%">
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 card-lift">
    <h2 class="font-bold text-gray-900 mb-1 flex items-center gap-2"><i class="fas fa-ticket-alt text-brand-500"></i> <?php echo e(__('subscriptions.redeem_voucher')); ?></h2>
    <p class="text-sm text-gray-500 mb-4"><?php echo e(__('subscriptions.redeem_hint')); ?></p>
    <form method="POST" action="<?php echo e(route('vouchers.redeem')); ?>" class="flex items-center gap-3 max-w-lg">
        <?php echo csrf_field(); ?>
        <input type="text" name="code" placeholder="<?php echo e(__('subscriptions.enter_code')); ?>" required
            class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-mono uppercase placeholder:normal-case placeholder:font-sans focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            style="letter-spacing: 0.15em;">
        <button type="submit" class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2 flex-shrink-0">
            <i class="fas fa-check text-xs"></i> <?php echo e(__('subscriptions.redeem')); ?>

        </button>
    </form>
</div>


<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-history text-brand-500"></i> <?php echo e(__('subscriptions.history_title')); ?></h2>
        <span class="text-xs text-gray-400"><?php echo e(__('subscriptions.count_records', ['count' => $subscriptions->count()])); ?></span>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3"><?php echo e(__('common.plan')); ?></th>
                <th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('subscriptions.starts')); ?></th>
                <th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('subscriptions.ends')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.status')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-brand-50 flex items-center justify-center"><i class="fas fa-box text-brand-400 text-xs"></i></div>
                        <span class="font-medium text-gray-900 text-xs"><?php echo e($sub->plan?->name ?? __('common.none')); ?></span>
                    </div>
                </td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-600"><?php echo e($sub->starts_at?->format('d M Y H:i') ?? '-'); ?></td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-600"><?php echo e($sub->ends_at?->format('d M Y H:i') ?? '-'); ?></td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                        <?php echo e($sub->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ''); ?>

                        <?php echo e($sub->status === 'expired' ? 'bg-gray-100 text-gray-600' : ''); ?>

                        <?php echo e($sub->status === 'inactive' ? 'bg-amber-50 text-amber-700' : ''); ?>">
                        <?php echo e($sub->status === 'active' ? __('common.active') : ($sub->status === 'expired' ? __('common.expired') : ucfirst($sub->status))); ?>

                    </span>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="4" class="px-5 py-12 text-center">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-history text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 text-sm"><?php echo e(__('subscriptions.no_history')); ?></p>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\subscriptions\index.blade.php ENDPATH**/ ?>