<?php $__env->startSection('title', __('common.plan') . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('plans.page_title')); ?></h1>
    <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('plans.subtitle')); ?></p>
</div>

<?php if($currentPlan): ?>
<div class="mb-6 bg-gradient-to-r from-brand-50 to-blue-50 border border-brand-200 rounded-2xl px-5 py-4 flex items-center justify-between flex-wrap gap-3">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center"><i class="fas fa-crown text-brand-600"></i></div>
        <div>
            <span class="text-sm text-brand-800"><?php echo e(__('common.plan')); ?> <?php echo e(__('common.active')); ?>: <strong class="text-base"><?php echo e($currentPlan->name); ?></strong></span>
            <div class="text-xs text-brand-600 mt-0.5">
                <span><i class="fas fa-mobile-alt mr-1"></i> <?php echo e($currentPlan->max_sessions); ?> <?php echo e(__('common.session')); ?></span>
                <span class="mx-2">·</span>
                <span><i class="fas fa-address-book mr-1"></i> <?php echo e(number_format($currentPlan->max_contacts)); ?> <?php echo e(__('common.contact')); ?></span>
                <span class="mx-2">·</span>
                <span><i class="fas fa-robot mr-1"></i> <?php echo e($currentPlan->max_autoreplies); ?> auto-reply</span>
            </div>
        </div>
    </div>
    <span class="text-xs bg-brand-100 text-brand-700 px-3 py-1.5 rounded-full font-medium"><?php echo e(__('common.active')); ?></span>
</div>
<?php endif; ?>

<div class="grid md:grid-cols-3 gap-5">
    <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $isActive = $currentPlan && $currentPlan->id === $plan->id;
        $colors = [
            'free' => ['bg-gradient-to-b from-gray-50 to-white', 'bg-gray-100 text-gray-600', 'text-gray-400'],
            'growth' => ['bg-gradient-to-b from-brand-50 to-white', 'bg-brand-100 text-brand-600', 'text-brand-300'],
            'enterprise' => ['bg-gradient-to-b from-violet-50 to-white', 'bg-violet-100 text-violet-600', 'text-violet-300'],
        ][$plan->slug] ?? ['', '', ''];
    ?>
    <div class="bg-white rounded-2xl border <?php echo e($isActive ? 'border-brand-400 ring-2 ring-brand-100 shadow-lg' : 'border-gray-200'); ?> overflow-hidden card-lift flex flex-col relative">
        <?php if($isActive): ?>
        <div class="absolute top-3 right-3 bg-brand-500 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full"><?php echo e(__('common.active')); ?></div>
        <?php endif; ?>

        <div class="p-6 <?php echo e($colors[0]); ?>">
            <div class="w-12 h-12 rounded-xl <?php echo e($colors[1]); ?> flex items-center justify-center mb-3">
                <i class="fas <?php echo e($plan->slug === 'free' ? 'fa-gift' : ($plan->slug === 'growth' ? 'fa-rocket' : 'fa-building')); ?> text-xl <?php echo e($colors[2]); ?>"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900"><?php echo e($plan->name); ?></h2>
            <div class="mt-2">
                <span class="text-3xl font-extrabold text-gray-900">
                    <?php if($plan->price > 0): ?> Rp <?php echo e(number_format($plan->price, 0, ',', '.')); ?> <?php else: ?> <?php echo e(__('common.free')); ?> <?php endif; ?>
                </span>
                <?php if($plan->price > 0): ?>
                <span class="text-sm text-gray-500">/ bln</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-6 space-y-2.5 flex-1">
            <?php $__currentLoopData = [
                ['fas fa-mobile-alt', $plan->max_sessions . ' ' . __('common.session') . ' WhatsApp'],
                ['fas fa-address-book', number_format($plan->max_contacts) . ' ' . __('common.contact')],
                ['fas fa-robot', $plan->max_autoreplies . ' Auto-Reply Rules'],
                ['fas fa-bullhorn', number_format($plan->max_campaign_recipients) . ' ' . __('common.receiver') . '/Kampanye'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$icon, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center gap-2.5 text-sm">
                <div class="w-5 h-5 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-[10px] text-emerald-500"></i>
                </div>
                <div class="flex items-center gap-2 text-gray-700"><i class="<?php echo e($icon); ?> w-4 text-center text-gray-400"></i> <?php echo e($label); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($plan->features): ?>
                <div class="pt-2 mt-2 border-t border-gray-100"></div>
                <?php $__currentLoopData = $plan->features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-2.5 text-sm">
                    <div class="w-5 h-5 rounded-full bg-brand-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-[10px] text-brand-500"></i>
                    </div>
                    <span class="text-gray-600"><?php echo e($feat); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </div>

        <div class="p-6 pt-0">
            <?php if($isActive): ?>
            <button disabled class="w-full bg-gray-100 text-gray-500 rounded-xl py-3 font-semibold text-sm cursor-not-allowed flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i> <?php echo e(__('common.plan')); ?> <?php echo e(__('common.active')); ?>

            </button>
            <?php else: ?>
            <form method="POST" action="<?php echo e(route('plans.subscribe', $plan)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-3 font-semibold text-sm hover:bg-brand-700 transition shadow-sm flex items-center justify-center gap-2">
                    <?php echo e($plan->price > 0 ? __('common.select') . ' ' . __('common.plan') : __('plans.activate_free')); ?> <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\plans\index.blade.php ENDPATH**/ ?>